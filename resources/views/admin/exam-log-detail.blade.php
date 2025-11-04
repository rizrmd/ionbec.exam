@extends('admin-layout')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.exam-logs') }}">Exam Logs</a>
                    </li>
                    <li class="breadcrumb-item active">Log Details</li>
                </ol>
            </nav>

            <!-- Log Details -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Log Details #{{ $log->id }}</h5>
                    @if ($log->is_suspicious())
                        <span class="badge bg-danger">
                            <i class="fas fa-exclamation-triangle"></i> Suspicious Activity
                        </span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <h6>Basic Information</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>ID:</strong></td>
                                    <td>{{ $log->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Created At:</strong></td>
                                    <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Session Key:</strong></td>
                                    <td><code>{{ $log->session_key }}</code></td>
                                </tr>
                                <tr>
                                    <td><strong>Tab Count:</strong></td>
                                    <td>
                                        <span class="badge {{ $log->tab_count_color }}">
                                            {{ $log->tab_count }} tab{{ $log->tab_count > 1 ? 's' : '' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Notes:</strong></td>
                                    <td>{{ $log->notes ?: 'No notes' }}</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Network Information -->
                        <div class="col-md-6">
                            <h6>Network Information</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>IP Address:</strong></td>
                                    <td>
                                        <code>{{ $log->ip_address }}</code>
                                        @if (\App\Services\GeolocationService::isSuspiciousIp($log->ip_address))
                                            <br><span class="text-danger">🚨 Suspicious IP</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Country:</strong></td>
                                    <td>{{ $log->country ?: 'Unknown' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>City:</strong></td>
                                    <td>{{ $log->city ?: 'Unknown' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>ISP:</strong></td>
                                    <td>{{ $log->isp ?: 'Unknown' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Location:</strong></td>
                                    <td>{{ $log->formatted_location }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- User Information -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6>Candidate Information</h6>
                            @if ($log->attempt && $log->attempt->taker)
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Candidate Name:</strong></td>
                                        <td>{{ $log->attempt->taker->name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Attempt ID:</strong></td>
                                        <td>{{ $log->attempt_id }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Attempt Started:</strong></td>
                                        <td>{{ $log->attempt->started_at?->format('Y-m-d H:i:s') ?: 'Unknown' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Candidate ID:</strong></td>
                                        <td>{{ $log->attempt->taker->id }}</td>
                                    </tr>
                                </table>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    No candidate information available for this log entry.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Technical Information -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6>Technical Information</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>User Agent:</strong></td>
                                        <td>
                                            <code class="small">{{ $log->truncated_user_agent }}</code>
                                            @if ($log->user_agent && strlen($log->user_agent) > 100)
                                                <button class="btn btn-sm btn-outline-primary ml-2"
                                                        onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'none' ? 'block' : 'none'">
                                                    Show Full
                                                </button>
                                                <div style="display: none; margin-top: 10px;">
                                                    <code>{{ $log->user_agent }}</code>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Session Info:</strong></td>
                                        <td>
                                            <pre class="small">{{ json_encode($log->session_info, JSON_PRETTY_PRINT) }}</pre>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Related Logs -->
                    @if ($log->session_key)
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6>Related Logs (Same Session)</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Time</th>
                                                <th>Tab Count</th>
                                                <th>Event Type</th>
                                                <th>Notes</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $relatedLogs = \App\Models\ExamSessionLog::where('session_key', $log->session_key)
                                                    ->where('id', '!=', $log->id)
                                                    ->orderBy('created_at', 'desc')
                                                    ->limit(10)
                                                    ->get();
                                            @endphp
                                            @if ($relatedLogs->count() > 0)
                                                @foreach ($relatedLogs as $relatedLog)
                                                    <tr>
                                                        <td>{{ $relatedLog->created_at->format('H:i:s') }}</td>
                                                        <td>
                                                            <span class="badge {{ $relatedLog->tab_count_color }}">
                                                                {{ $relatedLog->tab_count }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $relatedLog->notes ?: '-' }}</td>
                                                        <td>
                                                            <a href="{{ route('admin.exam-logs.show', $relatedLog) }}"
                                                               class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted">
                                                        No related logs found
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-3">
                <a href="{{ route('admin.exam-logs') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Logs
                </a>
                @if (auth()->user()->canManageLogs())
                    <button class="btn btn-danger" onclick="confirmDelete()">
                        <i class="fas fa-trash"></i> Delete Log
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
@if (auth()->user()->canManageLogs())
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this log entry? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('admin.exam-logs.delete', $log) }}" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete() {
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
@endif

<style>
code {
    background-color: #f1f1f1;
    padding: 2px 4px;
    border-radius: 3px;
    font-size: 0.85em;
}

pre {
    background-color: #f1f1f1;
    padding: 10px;
    border-radius: 5px;
    max-height: 200px;
    overflow-y: auto;
}

.table th {
    border-top: none;
    font-weight: 600;
    background-color: #f8f9fa;
    width: 150px;
}
</style>
@endsection