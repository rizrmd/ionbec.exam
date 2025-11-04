@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Exam Session Logs</h1>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.exam-logs.export') }}" class="btn btn-success">
                        <i class="fas fa-download"></i> Export CSV
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Logs</h5>
                            <h3>{{ number_format($stats['total_logs']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h5 class="card-title">Multiple Tabs</h5>
                            <h3>{{ number_format($stats['multiple_tab_logs']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5 class="card-title">Unique IPs</h5>
                            <h3>{{ number_format($stats['unique_ips']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-secondary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Today's Logs</h5>
                            <h3>{{ number_format($stats['today_logs']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Filters</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.exam-logs') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="date_from">Date From</label>
                                <input type="date" id="date_from" name="date_from" class="form-control"
                                       value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="date_to">Date To</label>
                                <input type="date" id="date_to" name="date_to" class="form-control"
                                       value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="ip_address">IP Address</label>
                                <input type="text" id="ip_address" name="ip_address" class="form-control"
                                       value="{{ request('ip_address') }}" placeholder="Search IP...">
                            </div>
                            <div class="col-md-3">
                                <label>&nbsp;</label>
                                <div class="form-check">
                                    <input type="checkbox" id="suspicious_only" name="suspicious_only"
                                           class="form-check-input" value="true"
                                           {{ request('suspicious_only') === 'true' ? 'checked' : '' }}>
                                    <label for="suspicious_only" class="form-check-label">
                                        Show suspicious only
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Apply Filters
                                </button>
                                <a href="{{ route('admin.exam-logs') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Logs Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Session Logs</h5>
                </div>
                <div class="card-body">
                    @if ($logs->count() === 0)
                        <div class="text-center py-4">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No logs found matching your criteria.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Candidate</th>
                                        <th>Session Key</th>
                                        <th>IP Address</th>
                                        <th>Location</th>
                                        <th>Tab Count</th>
                                        <th>ISP</th>
                                        <th>Notes</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($logs as $log)
                                        <tr class="{{ $log->is_suspicious() ? 'table-danger' : '' }}">
                                            <td>{{ $log->created_at->format('M j, Y H:i:s') }}</td>
                                            <td>
                                                @if ($log->attempt && $log->attempt->taker)
                                                    {{ $log->attempt->taker->name }}
                                                    <small class="text-muted d-block">
                                                        Attempt #{{ $log->attempt_id }}
                                                    </small>
                                                @else
                                                    <span class="text-muted">Unknown</span>
                                                    @if ($log->attempt_id)
                                                        <small class="text-muted d-block">
                                                            #{{ $log->attempt_id }}
                                                        </small>
                                                    @endif
                                                @endif
                                            </td>
                                            <td>
                                                <code class="small">{{ substr($log->session_key, 0, 20) }}...</code>
                                            </td>
                                            <td>
                                                <code>{{ $log->ip_address }}</code>
                                                @if ($log->ip_address)
                                                    <br>
                                                    <small class="text-muted">
                                                        {{ \App\Services\GeolocationService::isSuspiciousIp($log->ip_address) ? '🚨 Suspicious' : '✅ Normal' }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td>{{ $log->formatted_location }}</td>
                                            <td>
                                                <span class="badge {{ $log->tab_count_color }}">
                                                    {{ $log->tab_count }} tab{{ $log->tab_count > 1 ? 's' : '' }}
                                                </span>
                                            </td>
                                            <td>
                                                <small>{{ \Illuminate\Support\Str::limit($log->isp, 30) }}</small>
                                            </td>
                                            <td>
                                                @if ($log->notes)
                                                    <small class="text-muted">{{ \Illuminate\Support\Str::limit($log->notes, 50) }}</small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.exam-logs.show', $log) }}"
                                                   class="btn btn-sm btn-outline-primary" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }}
                                of {{ $logs->total() }} entries
                            </div>
                            {{ $logs->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom CSS for better table styling -->
<style>
.table-danger {
    background-color: #f8d7da !important;
}

.badge {
    font-size: 0.75em;
}

.table th {
    border-top: none;
    font-weight: 600;
    background-color: #f8f9fa;
}

.table-responsive {
    max-height: 600px;
    overflow-y: auto;
}

code {
    font-size: 0.85em;
    background-color: #f1f1f1;
    padding: 2px 4px;
    border-radius: 3px;
}
</style>
@endsection