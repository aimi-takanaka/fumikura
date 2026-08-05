<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.public')] #[Title('利用規約')] class extends Component
{
    //
}; ?>

<div class="mx-auto max-w-3xl">
    <a href="{{ route('home') }}" wire:navigate class="btn btn-ghost btn-sm mb-6">
        ← トップへ戻る
    </a>

    <h1 class="text-3xl font-bold">利用規約</h1>

    <p class="mt-4 leading-relaxed text-base-content/80">
        この利用規約（以下「本規約」）は、「文倉」（以下「本サービス」）の利用条件を定めるものです。本サービスをご利用いただく方（以下「ユーザー」）は、本規約に同意の上、本サービスをご利用いただきます。
    </p>

    <div class="mt-10 space-y-10 leading-relaxed text-base-content/80">
        <section>
            <h2 class="text-xl font-semibold text-base-content">第1条（適用）</h2>
            <p class="mt-3">
                本規約は、ユーザーと本サービス運営者（以下「運営者」）との間の、本サービスの利用に関わる一切の関係に適用されるものとします。
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-base-content">第2条（利用登録）</h2>
            <div class="mt-3 space-y-3">
                <p>
                    本サービスの一部機能は、登録希望者が本規約に同意の上、運営者の定める方法によって利用登録を申請し、運営者がこれを承認することによって利用開始となります。
                </p>
                <p>
                    運営者は、利用登録の申請者に以下の事由があると判断した場合、利用登録の申請を承認しないことがあります。
                </p>
                <ul class="list-disc space-y-1 pl-5">
                    <li>虚偽の事項を届け出た場合</li>
                    <li>本規約に違反したことがある者からの申請である場合</li>
                    <li>その他、運営者が利用登録を相当でないと判断した場合</li>
                </ul>
            </div>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-base-content">第3条（投稿コンテンツについて）</h2>
            <div class="mt-3 space-y-3">
                <p>
                    ユーザーは、本サービスを通じて、物語・アイデア投稿・下書き等のコンテンツ（以下「投稿コンテンツ」）を投稿できます。
                </p>
                <p>投稿コンテンツの著作権は、投稿したユーザーに帰属します。</p>
                <p>
                    ユーザーは、運営者に対し、投稿コンテンツを本サービスの提供・維持・改善・宣伝広告等の目的で使用（複製、公衆送信、翻案等を含む）することを許諾するものとします。
                </p>
                <p>
                    下書きとして保存された非公開の投稿コンテンツは、本人以外には公開されません。ただし、法令に基づく開示請求があった場合等、正当な理由がある場合はこの限りではありません。
                </p>
            </div>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-base-content">第4条（AIを用いた添削機能について）</h2>
            <div class="mt-3 space-y-3">
                <p>
                    本サービスは、投稿コンテンツの添削支援のため、外部の生成AIサービス（Google Gemini API）を利用しています。
                </p>
                <p>
                    ユーザーが添削機能を利用した場合、当該投稿コンテンツの本文はGoogle社のサーバーに送信され、添削結果の生成に使用されます。
                </p>
                <p>
                    運営者は無料利用枠のAPIを使用している場合があり、その場合、送信されたデータがGoogle社のAIモデルの学習等に利用される可能性があります。有料枠への切り替え等によりこの取り扱いが変更された場合は、本規約または本サービス内の告知にて改めてお知らせします。
                </p>
                <p>
                    ユーザーは、第三者の著作物の無断転載など、他者の権利を侵害する内容や、機密性の高い個人情報を含む文章を添削機能に入力しないものとします。
                </p>
                <p>
                    AIによる添削結果の正確性・妥当性について、運営者は保証しません。添削結果の採用可否は、ユーザー自身の判断で行ってください。
                </p>
            </div>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-base-content">第5条（禁止事項）</h2>
            <div class="mt-3 space-y-3">
                <p>ユーザーは、本サービスの利用にあたり、以下の行為をしてはなりません。</p>
                <ul class="list-disc space-y-1 pl-5">
                    <li>法令または公序良俗に違反する行為</li>
                    <li>犯罪行為に関連する行為</li>
                    <li>運営者、他のユーザー、または第三者の知的財産権、肖像権、プライバシー、名誉その他の権利または利益を侵害する行為</li>
                    <li>本サービスのサーバーまたはネットワークの機能を破壊・妨害する行為</li>
                    <li>本サービスの運営を妨害するおそれのある行為</li>
                    <li>不正アクセスをし、またはこれを試みる行為</li>
                    <li>他のユーザーに関する個人情報等を収集または蓄積する行為</li>
                    <li>他のユーザーに成りすます行為</li>
                    <li>運営者が許諾しない本サービス上での宣伝、広告、勧誘、または営業行為</li>
                    <li>面識のない異性との出会いを目的とした行為</li>
                    <li>その他、運営者が不適切と判断する行為</li>
                </ul>
            </div>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-base-content">第6条（本サービスの提供の停止等）</h2>
            <div class="mt-3 space-y-3">
                <p>
                    運営者は、以下のいずれかの事由があると判断した場合、ユーザーに事前に通知することなく本サービスの全部または一部の提供を停止または中断することができるものとします。
                </p>
                <ul class="list-disc space-y-1 pl-5">
                    <li>本サービスにかかるシステムの保守点検または更新を行う場合</li>
                    <li>地震、落雷、火災、停電または天災などの不可抗力により、本サービスの提供が困難となった場合</li>
                    <li>コンピュータまたは通信回線等が事故により停止した場合</li>
                    <li>その他、運営者が本サービスの提供が困難と判断した場合</li>
                </ul>
            </div>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-base-content">第7条（利用制限および登録抹消）</h2>
            <div class="mt-3 space-y-3">
                <p>
                    運営者は、ユーザーが以下のいずれかに該当する場合、事前の通知なく、投稿コンテンツの削除、ユーザーに対する本サービスの全部もしくは一部の利用を制限し、またはユーザーとしての登録を抹消することができるものとします。
                </p>
                <ul class="list-disc space-y-1 pl-5">
                    <li>本規約のいずれかの条項に違反した場合</li>
                    <li>登録事項に虚偽の事実があることが判明した場合</li>
                    <li>その他、運営者が本サービスの利用を適当でないと判断した場合</li>
                </ul>
            </div>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-base-content">第8条（保証の否認および免責事項）</h2>
            <div class="mt-3 space-y-3">
                <p>
                    運営者は、本サービスに事実上または法律上の瑕疵（安全性、信頼性、正確性、完全性、有効性、特定の目的への適合性、セキュリティなどに関する欠陥、エラーやバグ、権利侵害などを含みます）がないことを明示的にも黙示的にも保証しておりません。
                </p>
                <p>
                    運営者は、本サービスに起因してユーザーに生じたあらゆる損害について、運営者の故意または重過失による場合を除き、一切の責任を負いません。
                </p>
                <p>
                    運営者は、本サービスに関して、ユーザーと他のユーザーまたは第三者との間において生じた取引、連絡、紛争等について一切責任を負いません。
                </p>
            </div>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-base-content">第9条（サービス内容の変更等）</h2>
            <p class="mt-3">
                運営者は、ユーザーへの事前の告知をもって、本サービスの内容を変更、追加または廃止することがあり、ユーザーはこれを承諾するものとします。
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-base-content">第10条（利用規約の変更）</h2>
            <p class="mt-3">
                運営者は、必要と判断した場合には、ユーザーに通知することなく本規約を変更できるものとします。変更後の本規約は、本サービス内に掲示された時点より効力を生じるものとします。
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-base-content">第11条（個人情報の取扱い）</h2>
            <p class="mt-3">
                運営者は、本サービスの利用によって取得する個人情報については、別途定める
                <a href="{{ route('privacy') }}" class="link link-primary" wire:navigate>プライバシーポリシー</a>
                に従い適切に取り扱うものとします。
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-base-content">第12条（通知または連絡）</h2>
            <p class="mt-3">
                ユーザーと運営者との間の通知または連絡は、運営者の定める方法によって行うものとします。
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-base-content">第13条（権利義務の譲渡の禁止）</h2>
            <p class="mt-3">
                ユーザーは、運営者の書面による事前の承諾なく、利用契約上の地位または本規約に基づく権利もしくは義務を第三者に譲渡し、または担保に供することはできません。
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-base-content">第14条（準拠法・裁判管轄）</h2>
            <div class="mt-3 space-y-3">
                <p>本規約の解釈にあたっては、日本法を準拠法とします。</p>
                <p>
                    本サービスに関して紛争が生じた場合には、運営者の所在地を管轄する裁判所を専属的合意管轄とします。
                </p>
            </div>
        </section>
    </div>
</div>
