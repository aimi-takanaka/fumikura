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
     * Send a prompt to Gemini and return the response text.
     */
    private function generateContent(string $prompt): string
    {
        $apiKey = config('services.gemini.key');

        if (blank($apiKey)) {
            throw new RuntimeException('Gemini API key is not configured.');
        }

        $model = config('services.gemini.model');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        try {
            $response = $this->httpClient()
                ->withHeaders([
                    'x-goog-api-key' => $apiKey,
                ])
                ->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                ]);
        } catch (ConnectionException $exception) {
            throw new RuntimeException($exception->getMessage(), previous: $exception);
        }

        if ($response->failed()) {
            throw new RuntimeException('Gemini API request failed.');
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
一人称は「わたし」、語尾は「〜だよ」「〜だね」など柔らかく。
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
評価するのではなく、「この先が気になる」という期待感を伝えてください。

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
}
