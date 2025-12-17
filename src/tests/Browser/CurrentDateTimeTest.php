<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class CurrentDateTimeTest extends DuskTestCase
{
    use DatabaseMigrations;
    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function test_current_datetime_is_displayed_in_same_format_as_ui()
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                // パスを直接書かず、ルート名でアクセス
                ->visitRoute('user.attendance.attend')
                ->waitFor('#current-date', 10);

            // JavaScriptで時刻を 2025年12月1日(月) に固定
            $browser->script(<<<'JS'
                const mockDate = new Date(2025, 11, 1, 10, 0, 0); // 11は12月
                window.Date = class extends Date {
                    constructor() { return mockDate; }
                };
                updateClock(); // View側の関数を再実行して即時反映
JS);

            // 反映を待つ
            $browser->pause(500);

            // 正しい形式で表示されているか検証
            $browser->assertSeeIn('#current-date', '2025年12月1日(月)');
        });
    }
}
