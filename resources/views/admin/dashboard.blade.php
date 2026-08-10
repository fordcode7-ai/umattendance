@extends('layouts.app')

@section('title', 'Admin Dashboard | UM Attendance')

@section('content')
<div class="page-panel">
    <div class="large-box">
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 20px; align-items: flex-start;">
            <div style="min-width: 240px; flex: 1 1 320px;">
                <h2 class="page-title" style="font-size: clamp(2rem, 3vw, 2.8rem); margin-bottom: 0.4rem;">Super Admin Dashboard</h2>
                <p class="hero-copy" style="margin:0; color: rgba(247,245,242,0.86);">Manage both sport divisions and monitor student performance across the system.</p>
            </div>

            <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
                <form id="month-form" method="GET" action="/admin/dashboard" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
                    <input type="hidden" name="search" value="{{ $search }}" />
                    <select id="month-select" name="month" class="form-select" style="min-width: 130px; max-width: 140px; padding: 12px 14px; color:#111;background:#fff;">
                        @foreach(range(1, 12) as $optionMonth)
                            <option value="{{ $optionMonth }}" {{ $optionMonth === $month ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $optionMonth, 1)) }}</option>
                        @endforeach
                    </select>
                    <select id="year-select" name="year" class="form-select" style="min-width: 110px; max-width: 120px; padding: 12px 14px; color:#111;background:#fff;">
                        @foreach([$year - 1, $year, $year + 1] as $optionYear)
                            <option value="{{ $optionYear }}" {{ $optionYear === $year ? 'selected' : '' }}>{{ $optionYear }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="button button-secondary" style="min-width: 72px; padding: 11px 14px;">Go</button>
                </form>

                <div style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
                    <a href="/admin/dashboard?year={{ $year }}&month={{ max(1, $month - 1) }}" class="button button-secondary" style="padding: 10px 14px;">Prev</a>
                    <a href="/admin/dashboard?year={{ $year }}&month={{ min(12, $month + 1) }}" class="button button-secondary" style="padding: 10px 14px;">Next</a>
                </div>
            </div>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-top:14px;">
            <div style="display:flex;align-items:center;gap:12px;">
                <button type="button" class="icon-btn" id="search-toggle" aria-label="Toggle student search" style="width: 42px; height: 42px; border-radius: 16px; display: inline-flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12);">
                    <i class="bi bi-search" style="font-size: 1.05rem; color: #f3f3f3;"></i>
                </button>
                <span style="color: rgba(247, 245, 242, 0.88); font-size: 0.95rem;">Search student</span>
            </div>

            <div class="status-key">
                <span><span class="dot red"></span> Red = 2+ absences</span>
                <span><span class="dot yellow"></span> 3+ lates</span>
                <span><span class="dot green"></span> Good performance</span>
            </div>
        </div>

        <form method="GET" id="search-form" style="display: none; width: 100%; gap: 10px; flex-wrap: wrap; align-items: center; margin-top: 12px;">
            <input type="hidden" name="year" value="{{ $year }}" />
            <input type="hidden" name="month" value="{{ $month }}" />
            <input id="search-input" type="text" name="search" value="{{ $search }}" placeholder="Search student" class="form-control" style="flex: 1 1 240px; min-width: 220px; max-width: 100%;" />
            <button class="button button-primary" type="submit" style="min-width: 84px; padding: 12px 16px;">Find</button>
        </form>

        <div style="display:grid;grid-template-columns:1fr;gap:28px;margin-top:18px;">
            <section>
                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
                    <h3 style="margin:0;color:#fff;font-size:1.1rem;">Taekwondo Students</h3>
                    <span style="padding:4px 10px;border-radius:999px;background:rgba(244,180,0,0.12);color:#f4b400;font-size:0.82rem;">Taekwondo</span>
                </div>
                <div class="roster-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 14px; margin-top: 14px;">
                    @forelse($taekwondoStudents as $student)
                    @php
                        $colorHex = $student['color'] === 'red' ? '#d43945' : ($student['color'] === 'yellow' ? '#f4b400' : '#18a062');
                        [$r, $g, $b] = sscanf($colorHex, "#%02x%02x%02x");
                        $glow = "0 6px 18px rgba($r,$g,$b,0.06)";
                        $cardBg = 'rgba(255,255,255,0.03)';
                        $borderColor = 'rgba(255,255,255,0.06)';
                    @endphp
                    <div class="student-card" style="border-radius: 22px; padding: 16px; background: {{ $cardBg }}; border: 1px solid {{ $borderColor }}; box-shadow: 0 2px 8px rgba(0,0,0,0.12), {{ $glow }}; border-left: 6px solid {{ $colorHex }};">
                        @if(!empty($student['avatar']))
                            <img src="{{ $student['avatar'] }}" alt="{{ $student['first_name'] }} {{ $student['last_name'] }}" class="avatar avatar-image" style="width:52px;height:52px;object-fit:cover;border-radius:12px;" />
                        @else
                            <div class="avatar" style="width:52px;height:52px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;font-weight:700;background:rgba(255,255,255,0.08);color:#fff;">{{ strtoupper(substr($student['first_name'],0,1)) }}{{ strtoupper(substr($student['last_name'],0,1)) }}</div>
                        @endif
                        <div class="student-info" style="min-width:0;margin-left:12px;">
                            <h3 style="margin:0 0 6px;font-size:1rem;line-height:1.2;">{{ $student['first_name'] }} {{ $student['last_name'] }}</h3>
                            <p style="margin:0;color:rgba(247,245,242,0.72);font-size:0.88rem;">{{ $student['student_id'] }} · {{ ucfirst($student['sport']) }} · {{ $student['year_level'] }} · {{ $student['course'] }}</p>
                        </div>
                        <div class="stat-grid" style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin-top:14px;">
                            <div style="padding:14px;border-radius:18px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);">
                                <span style="display:block;font-size:0.78rem;color:rgba(247,245,242,0.68);margin-bottom:6px;">Present</span>
                                <strong style="font-size:1.2rem;">{{ $student['counts']['present'] }}</strong>
                            </div>
                            <div style="padding:14px;border-radius:18px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);">
                                <span style="display:block;font-size:0.78rem;color:rgba(247,245,242,0.68);margin-bottom:6px;">Late</span>
                                <strong style="font-size:1.2rem;">{{ $student['counts']['late'] }}</strong>
                            </div>
                            <div style="padding:14px;border-radius:18px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);">
                                <span style="display:block;font-size:0.78rem;color:rgba(247,245,242,0.68);margin-bottom:6px;">Absent</span>
                                <strong style="font-size:1.2rem;">{{ $student['counts']['absent'] }}</strong>
                            </div>
                            <div style="padding:14px;border-radius:18px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);">
                                <span style="display:block;font-size:0.78rem;color:rgba(247,245,242,0.68);margin-bottom:6px;">Excuse</span>
                                <strong style="font-size:1.2rem;">{{ $student['counts']['excuse'] }}</strong>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div style="grid-column: 1 / -1; padding: 18px 16px; border-radius: 20px; background: rgba(255,255,255,0.05); color: rgba(247,245,242,0.82);">
                        No Taekwondo students found for this month.
                    </div>
                    @endforelse
                </div>
            </section>

            <section>
                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
                    <h3 style="margin:0;color:#fff;font-size:1.1rem;">Karatedo Students</h3>
                    <span style="padding:4px 10px;border-radius:999px;background:rgba(24,160,98,0.12);color:#18a062;font-size:0.82rem;">Karatedo</span>
                </div>
                <div class="roster-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 14px; margin-top: 14px;">
                    @forelse($karatedoStudents as $student)
                    @php
                        $colorHex = $student['color'] === 'red' ? '#d43945' : ($student['color'] === 'yellow' ? '#f4b400' : '#18a062');
                        [$r, $g, $b] = sscanf($colorHex, "#%02x%02x%02x");
                        $glow = "0 6px 18px rgba($r,$g,$b,0.06)";
                        $cardBg = 'rgba(255,255,255,0.03)';
                        $borderColor = 'rgba(255,255,255,0.06)';
                    @endphp
                    <div class="student-card" style="border-radius: 22px; padding: 16px; background: {{ $cardBg }}; border: 1px solid {{ $borderColor }}; box-shadow: 0 2px 8px rgba(0,0,0,0.12), {{ $glow }}; border-left: 6px solid {{ $colorHex }};">
                        @if(!empty($student['avatar']))
                            <img src="{{ $student['avatar'] }}" alt="{{ $student['first_name'] }} {{ $student['last_name'] }}" class="avatar avatar-image" style="width:52px;height:52px;object-fit:cover;border-radius:12px;" />
                        @else
                            <div class="avatar" style="width:52px;height:52px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;font-weight:700;background:rgba(255,255,255,0.08);color:#fff;">{{ strtoupper(substr($student['first_name'],0,1)) }}{{ strtoupper(substr($student['last_name'],0,1)) }}</div>
                        @endif
                        <div class="student-info" style="min-width:0;margin-left:12px;">
                            <h3 style="margin:0 0 6px;font-size:1rem;line-height:1.2;">{{ $student['first_name'] }} {{ $student['last_name'] }}</h3>
                            <p style="margin:0;color:rgba(247,245,242,0.72);font-size:0.88rem;">{{ $student['student_id'] }} · {{ ucfirst($student['sport']) }} · {{ $student['year_level'] }} · {{ $student['course'] }}</p>
                        </div>
                        <div class="stat-grid" style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin-top:14px;">
                            <div style="padding:14px;border-radius:18px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);">
                                <span style="display:block;font-size:0.78rem;color:rgba(247,245,242,0.68);margin-bottom:6px;">Present</span>
                                <strong style="font-size:1.2rem;">{{ $student['counts']['present'] }}</strong>
                            </div>
                            <div style="padding:14px;border-radius:18px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);">
                                <span style="display:block;font-size:0.78rem;color:rgba(247,245,242,0.68);margin-bottom:6px;">Late</span>
                                <strong style="font-size:1.2rem;">{{ $student['counts']['late'] }}</strong>
                            </div>
                            <div style="padding:14px;border-radius:18px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);">
                                <span style="display:block;font-size:0.78rem;color:rgba(247,245,242,0.68);margin-bottom:6px;">Absent</span>
                                <strong style="font-size:1.2rem;">{{ $student['counts']['absent'] }}</strong>
                            </div>
                            <div style="padding:14px;border-radius:18px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);">
                                <span style="display:block;font-size:0.78rem;color:rgba(247,245,242,0.68);margin-bottom:6px;">Excuse</span>
                                <strong style="font-size:1.2rem;">{{ $student['counts']['excuse'] }}</strong>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div style="grid-column: 1 / -1; padding: 18px 16px; border-radius: 20px; background: rgba(255,255,255,0.05); color: rgba(247,245,242,0.82);">
                        No Karatedo students found for this month.
                    </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var toggle = document.getElementById('search-toggle');
        var form = document.getElementById('search-form');
        var input = document.getElementById('search-input');

        if (toggle && form) {
            toggle.addEventListener('click', function () {
                if (form.style.display === 'none' || form.style.display === '') {
                    form.style.display = 'flex';
                    input.focus();
                } else {
                    form.style.display = 'none';
                }
            });
        }
    });
</script>
@endsection
