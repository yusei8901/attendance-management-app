<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\AttendanceEditRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AttendanceEditTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    // 承認待ちの修正申請が全て表示されている
    public function test_admin_views_pending_edit_requests()
    {
        // 承認待ちの修正申請を3件作成
        $pendingRequests = AttendanceEditRequest::factory()
            ->pending()
            ->count(3)
            ->create();
        // 承認済みの修正申請を2件作成
        $approvedRequests = AttendanceEditRequest::factory()
            ->approved()
            ->count(2)
            ->create();
        // 管理者でログイン
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');
        // 承認待ちタブを開く
        $response = $this->get(route('admin.request.list', ['tab' => 'pending']))
            ->assertStatus(200);
        // 承認待ちの修正申請が表示されていること
        foreach ($pendingRequests as $request) {
            // remarks や理由など、画面に出ているカラムを assert する
            $response->assertSee(e($request->remarks));
        }
        // 承認済みは表示されないこと
        foreach ($approvedRequests as $request) {
            $response->assertDontSee(e($request->remarks));
        }
    }

    // 承認済みの修正申請が全て表示されている
    public function test_admin_views_approved_edit_requests()
    {
        // 承認待ちの修正申請を3件作成
        $pendingRequests = AttendanceEditRequest::factory()
            ->pending()
            ->count(3)
            ->create();
        // 承認済みの修正申請を2件作成
        $approvedRequests = AttendanceEditRequest::factory()
            ->approved()
            ->count(2)
            ->create();
        // 管理者でログイン
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');
        // 承認済みタブを開く
        $response = $this->get(route('admin.request.list', ['tab' => 'approved']))
            ->assertStatus(200);
        // 承認待ちの修正申請が表示されていること
        foreach ($approvedRequests as $request) {
            // remarks や理由など、画面に出ているカラムを assert する
            $response->assertSee(e($request->remarks));
        }
        // 承認済みは表示されないこと
        foreach ($pendingRequests as $request) {
            $response->assertDontSee(e($request->remarks));
        }
    }

    // 修正申請の詳細内容が正しく表示されている
    public function test_admin_views_edit_request_details()
    {
        // 修正申請データを1件作成
        $request = AttendanceEditRequest::factory()->create([
            'remarks' => '勤務時間の修正をお願いします',
            'old_start_time' => '09:00',
            'old_end_time'   => '18:00',
            'new_start_time' => '10:00',
            'new_end_time'   => '19:00',
            'status'         => 'pending',
        ]);
        // 管理者でログイン
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');
        // 詳細画面へアクセス
        $response = $this->get(route('admin.request.detail', $request->attendance_id))
            ->assertStatus(200);
        // 詳細画面に表示されるべき内容を確認
        $response->assertSee('勤務時間の修正をお願いします');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    // 修正申請の承認処理が正しく行われる
    public function test_admin_approves_edit_request_successfully()
    {
        // 出勤データと修正申請用データの作成
        $attendance = Attendance::factory()->create([
            'start_time' => '09:00',
            'end_time'   => '18:00',
            'work_time'  => 540, // 9時間
        ]);
        $request = AttendanceEditRequest::factory()->create([
            'attendance_id'  => $attendance->id,
            'old_start_time' => '09:00',
            'old_end_time'   => '18:00',
            'old_work_time'  => 540,
            'new_start_time' => '10:00',
            'new_end_time'   => '19:00',
            'new_work_time'  => 540,
            'status'         => 'pending',
        ]);
        // 管理者でログイン
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');
        // 承認処理を実行
        $response = $this->post(route('admin.request.approve', $request->attendance_id));
        $response->assertStatus(302);
        // 修正申請が "approved" に更新されたこと
        $this->assertDatabaseHas('attendance_edit_requests', [
            'id'     => $request->id,
            'status' => 'approved',
        ]);
        // attendance が新しい時間に更新されたこと
        $this->assertDatabaseHas('attendances', [
            'id'         => $attendance->id,
            'start_time' => '10:00:00',
            'end_time'   => '19:00:00',
        ]);
    }
}
