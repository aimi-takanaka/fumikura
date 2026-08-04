<?php

namespace App\Services;

use App\StoryStatus;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiProofreadingService
{
    /**
     * Ask Gemini to check the given text for typos only.
     */
    public function checkTypos(string $body): string
    {
        return $this->generateContent($this->buildTypoCheckPrompt($body));
    }

    /**
     * Ask Gemini to respond as Fumi after a story submission.
     */
    public function encourageStory(StoryStatus $status, string $topicTitle, string $storyText): string
    {
        return $this->generateContent($this->buildEncouragementPrompt($status, $topicTitle, $storyText));
    }

    /**
     * Ask Gemini for alternate-viewpoint story suggestions.
     *
     * @return list<array{viewpoint: string, hook: string, comment: string}>
     */
    public function suggestAnotherStories(string $topicTitle, string $storyText): array
    {
        $text = $this->generateContent(
            $this->buildAnotherStorySuggestionPrompt($topicTitle, $storyText),
            json: true,
        );

        return $this->parseAnotherStorySuggestions($text);
    }

    /**
     * Send a prompt to Gemini and return the response text.
     */
    private function generateContent(string $prompt, bool $json = false): string
    {
        $apiKey = config('services.gemini.key');

        if (blank($apiKey)) {
            throw new RuntimeException('Gemini API key is not configured.');
        }

        $model = config('services.gemini.model');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
        ];

        if ($json) {
            $payload['generationConfig'] = [
                'responseMimeType' => 'application/json',
            ];
        }

        try {
            $response = $this->httpClient()
                ->withHeaders([
                    'x-goog-api-key' => $apiKey,
                ])
                ->retry(2, 500, function ($exception) {
                    return $exception instanceof ConnectionException;
                }, throw: false)
                ->post($url, $payload);
        } catch (ConnectionException $exception) {
            throw new RuntimeException($exception->getMessage(), previous: $exception);
        }

        if ($response->failed()) {
            $details = trim($response->body());

            throw new RuntimeException(
                'Gemini API request failed.'.($details !== '' ? ' '.$details : ''),
            );
        }

        $text = data_get($response->json(), 'candidates.0.content.parts.0.text');

        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException('Gemini API returned an empty response.');
        }

        return trim($text);
    }

    /**
     * Build an HTTP client configured for outbound API requests.
     */
    private function httpClient(): PendingRequest
    {
        $client = Http::timeout(30);

        $caBundle = config('services.http.ca_bundle');

        if (is_string($caBundle) && $caBundle !== '' && file_exists($caBundle)) {
            return $client->withOptions(['verify' => $caBundle]);
        }

        return $client;
    }

    /**
     * Build the typo-check prompt for Gemini.
     */
    private function buildTypoCheckPrompt(string $body): string
    {
        return <<<PROMPT
あなたは日本語の校正者です。以下の文章について、誤字・脱字・タイプミスのみを指摘してください。
表現の改善や文体の添削はしないでください。
問題がなければ「誤字・脱字は見つかりませんでした。」とだけ答えてください。
問題がある場合は、箇条書きで簡潔に指摘してください。原文全体を書き直さないでください。

---
{$body}
---
PROMPT;
    }

    /**
     * Build the encouragement prompt for Gemini based on story status.
     */
    private function buildEncouragementPrompt(StoryStatus $status, string $topicTitle, string $storyText): string
    {
        $persona = $this->fumiPersonaPrompt();

        $taskPrompt = match ($status) {
            StoryStatus::Complete => $this->completeStoryEncouragementPrompt($topicTitle, $storyText),
            StoryStatus::Draft => $this->draftStoryEncouragementPrompt($topicTitle, $storyText),
            StoryStatus::Idea => $this->ideaStoryEncouragementPrompt($topicTitle, $storyText),
        };

        return $persona."\n\n".$taskPrompt;
    }

    /**
     * Build Fumi's shared persona instructions.
     */
    private function fumiPersonaPrompt(): string
    {
        return <<<'PROMPT'
あなたは創作プラットフォーム「文倉」のマスコットキャラクター、ウサギの「ふみ」です。
物語を書く人を見守り、応援する存在として、温かく親しみやすい口調で話します。
一人称は「ぼく」、語尾は「〜だよ」「〜だね」など柔らかく。
絵文字は使わず、句読点と「！」で感情を表現してください。
PROMPT;
    }

    /**
     * Build the encouragement prompt for completed stories.
     */
    private function completeStoryEncouragementPrompt(string $topicTitle, string $storyText): string
    {
        return <<<PROMPT
ユーザーが物語を「完成」として投稿しました。全力でお祝いしてください。

以下のいずれか1つの切り口をランダムに選び、3〜4文で感想を書いてください。
・情景描写や比喩表現の巧みさを具体的に褒める
・キャラクターの心情や関係性の描き方を褒める
・展開・構成・オチの意外性を褒める
・読み終えたあとの余韻や後味を褒める

必ず物語の中の具体的な要素（登場人物名、印象的な一文、場面など）に触れてください。
一般論だけの感想は禁止です。
最後は「完成おめでとう」というニュアンスの一言で締めてください。
毎回同じ言い回しを避けてください。

【お題タイトル】{$topicTitle}
【物語本文】{$storyText}
PROMPT;
    }

    /**
     * Build the encouragement prompt for draft stories.
     */
    private function draftStoryEncouragementPrompt(string $topicTitle, string $storyText): string
    {
        return <<<PROMPT
ユーザーが物語を「下書き」として投稿しました。まだ完成前の段階です。
評価するのではなく、「今のペースで大丈夫ですよ」という労いと、
「この先が気になります」という期待感の両方を伝えてください。
根を詰めすぎず、自分のペースで書き進めてほしいという温かさを含めてください。

以下を意識して2〜3文で書いてください。
・現時点で気になった伏線、設定、キャラクターに触れて「この先どうなるの？」と好奇心を示す
・「ここから育てていくのが楽しみ」というニュアンスを含める
・完成度を評価するような言い方（「良く書けている」等）は避け、あくまで"これから"に焦点を当てる

【お題タイトル】{$topicTitle}
【下書き本文】{$storyText}
PROMPT;
    }

    /**
     * Build the encouragement prompt for idea posts.
     */
    private function ideaStoryEncouragementPrompt(string $topicTitle, string $storyText): string
    {
        return <<<PROMPT
ユーザーが物語になる前の「アイデア」をひとこと投稿しました。

以下を2文程度で書いてください。
・そのアイデアの視点・切り口の面白さに共感する（「その発想いいね！」のニュアンス）
・最後に、他の人にも投稿を促すような一言を添える
　（例：「他の人はどう見るだろう？」「同じお題で違う発想も見てみたいな」など）

説教くさくならないよう、あくまで軽いノリで書いてください。

【お題タイトル】{$topicTitle}
【アイデア投稿】{$storyText}
PROMPT;
    }

    /**
     * Build the alternate-viewpoint suggestion prompt for Gemini.
     */
    private function buildAnotherStorySuggestionPrompt(string $topicTitle, string $storyText): string
    {
        $persona = $this->fumiPersonaPrompt();

        return <<<PROMPT
{$persona}

ユーザーが完成させた物語を読み、別の視点から見た物語のアイデアを1〜2個提案してください。

以下の手順で考えてください。
1. 本文に登場する人物（主人公以外）を洗い出す。人物が主人公しかいない場合は、
   「その場に居合わせたけれど描かれなかった人物」や「主人公の行動を外から見ていた人物」を想定してよい。
2. その人物の視点に立った場合、物語がどう違って見えるかを考える。
   （同じ出来事の裏側、知らなかった事情、逆の感情を抱いていた可能性など）
3. 元の物語のネタバレや結末の否定にならないよう、あくまで「もう一つの可能性」として提案する。

出力は以下のJSON形式で、日本語で返してください。

{
  "suggestions": [
    {
      "viewpoint": "（視点となる人物や存在。例：友人のA、物語に出てこない飼い猫、など）",
      "hook": "（15〜25文字程度の、続きが気になる一言フック）",
      "comment": "（40〜60文字程度。なぜこの視点が面白そうかを、ふみの口調で伝える）"
    }
  ]
}

suggestionsは最大2件までとします。
無理に2件出す必要はなく、良い提案が1件しか思いつかない場合は1件でも構いません。

【お題タイトル】{$topicTitle}
【完成した物語】{$storyText}
PROMPT;
    }

    /**
     * Parse Gemini's alternate-story suggestion JSON into a typed list.
     *
     * @return list<array{viewpoint: string, hook: string, comment: string}>
     */
    private function parseAnotherStorySuggestions(string $text): array
    {
        $decoded = json_decode($this->extractJsonPayload($text), true);

        if (! is_array($decoded)) {
            throw new RuntimeException('Gemini API returned invalid JSON for another story suggestions.');
        }

        $suggestions = data_get($decoded, 'suggestions');

        if (! is_array($suggestions)) {
            throw new RuntimeException('Gemini API returned no another story suggestions.');
        }

        $parsed = [];

        foreach (array_slice($suggestions, 0, 2) as $suggestion) {
            if (! is_array($suggestion)) {
                continue;
            }

            $viewpoint = trim((string) ($suggestion['viewpoint'] ?? ''));
            $hook = trim((string) ($suggestion['hook'] ?? ''));
            $comment = trim((string) ($suggestion['comment'] ?? ''));

            if ($viewpoint === '' || $hook === '' || $comment === '') {
                continue;
            }

            $parsed[] = [
                'viewpoint' => $viewpoint,
                'hook' => $hook,
                'comment' => $comment,
            ];
        }

        if ($parsed === []) {
            throw new RuntimeException('Gemini API returned empty another story suggestions.');
        }

        return $parsed;
    }

    /**
     * Extract a JSON object from a raw Gemini text response.
     */
    private function extractJsonPayload(string $text): string
    {
        $trimmed = trim($text);

        if (str_starts_with($trimmed, '```')) {
            $trimmed = preg_replace('/^```(?:json)?\s*/i', '', $trimmed) ?? $trimmed;
            $trimmed = preg_replace('/\s*```$/', '', $trimmed) ?? $trimmed;
            $trimmed = trim($trimmed);
        }

        return $trimmed;
    }
}
