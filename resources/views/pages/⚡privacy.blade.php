<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.public')] #[Title('プライバシーポリシー')] class extends Component
{
    //
}; ?>

<div class="mx-auto max-w-3xl">
    <flux:button :href="route('home')" variant="ghost" size="sm" icon="arrow-left" wire:navigate class="mb-6">
        トップへ戻る
    </flux:button>

    <flux:heading size="xl">プライバシーポリシー</flux:heading>

    <flux:text class="mt-4 leading-relaxed">
        「文倉」（以下「本サービス」）は、ユーザーの個人情報を以下のとおり取り扱います。本ポリシーは、本サービスの
        <flux:link :href="route('terms')" wire:navigate>利用規約</flux:link>
        第11条に基づき定めるものです。
    </flux:text>

    <div class="mt-10 space-y-10">
        <section>
            <flux:heading size="lg">第1条（取得する情報）</flux:heading>
            <div class="mt-3 space-y-3 leading-relaxed">
                <flux:text>本サービスは、ユーザーが本サービスを利用するにあたり、以下の情報を取得することがあります。</flux:text>
                <ul class="list-disc space-y-1 pl-5 text-sumi/80">
                    <li>登録情報：メールアドレス、ニックネーム、パスワード（暗号化して保存）</li>
                    <li>投稿情報：投稿された物語、アイデア投稿、下書き、コメント等のコンテンツ</li>
                    <li>利用状況の情報：アクセスログ、IPアドレス、Cookie、ブラウザの種類、閲覧・利用履歴</li>
                    <li>お問い合わせ情報：お問い合わせフォームに入力された氏名・連絡先・相談内容</li>
                </ul>
            </div>
        </section>

        <section>
            <flux:heading size="lg">第2条（利用目的）</flux:heading>
            <div class="mt-3 space-y-3 leading-relaxed">
                <flux:text>取得した情報は、以下の目的で利用します。</flux:text>
                <ul class="list-disc space-y-1 pl-5 text-sumi/80">
                    <li>本サービスの提供、維持、保護、改善のため</li>
                    <li>ユーザー認証、本人確認のため</li>
                    <li>AI添削機能など、本サービス内の各機能を提供するため</li>
                    <li>お問い合わせへの対応のため</li>
                    <li>利用規約に違反する行為への対応のため</li>
                    <li>上記の利用目的に付随する目的のため</li>
                </ul>
            </div>
        </section>

        <section>
            <flux:heading size="lg">第3条（AI添削機能における情報の外部送信について）</flux:heading>
            <div class="mt-3 space-y-3 leading-relaxed">
                <flux:text>
                    本サービスの添削機能は、外部の生成AIサービス（Google Gemini API）を利用して提供しています。
                </flux:text>
                <flux:text>
                    ユーザーが添削機能を利用した場合、当該投稿コンテンツの本文はGoogle社（Google LLC）のサーバーに送信され、添削結果の生成処理に使用されます。
                </flux:text>
                <flux:text>
                    運営者が無料利用枠のAPIを使用している場合、送信された文章はGoogle社のAIモデルの学習等に利用される可能性があります。この取り扱いは、Google社のプライバシーポリシーおよび利用規約に基づきます。
                </flux:text>
                <flux:text>
                    有料プランへの切り替え等により送信データの取り扱いが変更された場合は、本ポリシーを改定のうえ、本サービス内で告知します。
                </flux:text>
                <flux:text>
                    添削機能は任意の機能であり、利用するかどうかはユーザー自身が選択できます。氏名や連絡先など、第三者に知られたくない情報を添削対象の文章に含めないようご注意ください。
                </flux:text>
            </div>
        </section>

        <section>
            <flux:heading size="lg">第4条（Cookie等の利用）</flux:heading>
            <div class="mt-3 space-y-3 leading-relaxed">
                <flux:text>
                    本サービスは、ログイン状態の維持やアクセス解析のため、Cookieを利用することがあります。
                </flux:text>
                <flux:text>
                    ユーザーはブラウザの設定によりCookieの利用を無効にすることができますが、その場合、本サービスの一部機能が利用できなくなることがあります。
                </flux:text>
            </div>
        </section>

        <section>
            <flux:heading size="lg">第5条（第三者提供）</flux:heading>
            <div class="mt-3 space-y-3 leading-relaxed">
                <flux:text>
                    運営者は、次に掲げる場合を除き、あらかじめユーザーの同意を得ることなく、第三者に個人情報を提供しません。
                </flux:text>
                <ul class="list-disc space-y-1 pl-5 text-sumi/80">
                    <li>法令に基づく場合</li>
                    <li>人の生命、身体または財産の保護のために必要がある場合であって、本人の同意を得ることが困難であるとき</li>
                    <li>国の機関等が法令の定める事務を遂行することに対して協力する必要がある場合であって、本人の同意を得ることにより当該事務の遂行に支障を及ぼすおそれがあるとき</li>
                </ul>
            </div>
        </section>

        <section>
            <flux:heading size="lg">第6条（外部サービスの利用について）</flux:heading>
            <div class="mt-3 space-y-3 leading-relaxed">
                <flux:text>
                    本サービスは、機能の提供にあたり、以下のような外部サービスを利用する場合があります。それぞれのサービスにおける情報の取り扱いは、各社のプライバシーポリシーに従います。
                </flux:text>
                <ul class="list-disc space-y-1 pl-5 text-sumi/80">
                    <li>Google Gemini API（AI添削機能）</li>
                    <li>その他、本サービスに組み込まれるAPI・分析ツール等（導入時に本ポリシーへ追記します）</li>
                </ul>
            </div>
        </section>

        <section>
            <flux:heading size="lg">第7条（情報の保存期間）</flux:heading>
            <flux:text class="mt-3 leading-relaxed">
                運営者は、利用目的の達成に必要な範囲内において、個人情報を保存します。ユーザーが退会した場合、登録情報は合理的な期間内に削除しますが、法令上保存が必要な情報についてはこの限りではありません。
            </flux:text>
        </section>

        <section>
            <flux:heading size="lg">第8条（開示・訂正・削除等）</flux:heading>
            <div class="mt-3 space-y-3 leading-relaxed">
                <flux:text>
                    ユーザーは、運営者が保有する自己の個人情報について、開示、訂正、追加、削除、利用停止を求めることができます。
                </flux:text>
                <flux:text>
                    請求を希望する場合は、第11条のお問い合わせ窓口までご連絡ください。運営者は、本人確認の上、法令に従い合理的な期間内に対応します。
                </flux:text>
            </div>
        </section>

        <section>
            <flux:heading size="lg">第9条（未成年者の利用について）</flux:heading>
            <flux:text class="mt-3 leading-relaxed">
                未成年者が本サービスを利用する場合は、保護者の同意を得た上でご利用ください。
            </flux:text>
        </section>

        <section>
            <flux:heading size="lg">第10条（プライバシーポリシーの変更）</flux:heading>
            <flux:text class="mt-3 leading-relaxed">
                本ポリシーの内容は、法令その他本ポリシーに別段の定めのある事項を除いて、ユーザーに通知することなく変更することができるものとします。変更後のプライバシーポリシーは、本サービス内に掲示された時点より効力を生じるものとします。
            </flux:text>
        </section>

        <section>
            <flux:heading size="lg">第11条（お問い合わせ窓口）</flux:heading>
            <flux:text class="mt-3 leading-relaxed">
                本ポリシーに関するお問い合わせは、お問い合わせフォームまでご連絡ください。
            </flux:text>
        </section>
    </div>
</div>
