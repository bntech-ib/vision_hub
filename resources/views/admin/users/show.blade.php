@extends('admin.layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>User Details</h1>
        <div>
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Back to List</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">User Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> {{ $user->name }}</p>
                            <p><strong>Username:</strong> {{ $user->username ?? 'N/A' }}</p>
                            <p><strong>Email:</strong> {{ $user->email }}</p>
                            <p><strong>Phone:</strong> {{ $user->phone ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Country:</strong> {{ $user->country ?? 'N/A' }}</p>
                            <p><strong>Wallet Balance:</strong> ₦{{ number_format($user->wallet_balance, 2) }}</p>
                            <p><strong>Package:</strong> {{ $user->currentPackage->name ?? 'None' }}</p>
                            <p><strong>Package Expires:</strong> {{ $user->package_expires_at ? $user->package_expires_at->format('M d, Y') : 'N/A' }}</p>
                            <p><strong>Withdrawal Access:</strong> 
                                @if($user->hasWithdrawalAccess())
                                    <span class="badge bg-success">Enabled</span>
                                @else
                                    <span class="badge bg-danger">Disabled</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Full Name:</strong>
                        <p class="mt-2">{{ $user->full_name ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Bank Account Information</h5>
                </div>
                <div class="card-body">
                    @php
                        $bankDetails = $user->getAdminBankAccountDetails();
                    @endphp
                    
                    @if($bankDetails['has_bound_bank_account'])
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Account Holder Name:</strong> {{ $bankDetails['bank_account_holder_name'] ?? 'N/A' }}</p>
                                <p><strong>Bank Name:</strong> {{ $bankDetails['bank_name'] ?? 'N/A' }}</p>
                                <p><strong>Bank Branch:</strong> {{ $bankDetails['bank_branch'] ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Account Number:</strong> {{ $bankDetails['bank_account_number'] ?? 'N/A' }}</p>
                                <p><strong>Routing Number:</strong> {{ $bankDetails['bank_routing_number'] ?? 'N/A' }}</p>
                                <p><strong>Bound At:</strong> {{ $bankDetails['bank_account_bound_at'] ? $bankDetails['bank_account_bound_at']->format('M d, Y H:i') : 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Bank account details are encrypted for security and can only be updated by the user.
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> This user has not bound their bank account details yet.
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">User Relationships</h5>
                </div>
                <div class="card-body">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" id="userRelationshipsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="projects-tab" data-bs-toggle="tab" data-bs-target="#projects" type="button" role="tab">Projects ({{ $user->projects->count() }})</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="images-tab" data-bs-toggle="tab" data-bs-target="#images" type="button" role="tab">Images ({{ $user->images->count() }})</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="processing-jobs-tab" data-bs-toggle="tab" data-bs-target="#processing-jobs" type="button" role="tab">Processing Jobs ({{ $user->processingJobs->count() }})</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tags-tab" data-bs-toggle="tab" data-bs-target="#tags" type="button" role="tab">Tags ({{ $user->tags->count() }})</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="ads-tab" data-bs-toggle="tab" data-bs-target="#ads" type="button" role="tab">Advertisements ({{ $user->advertisements->count() }})</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="ad-interactions-tab" data-bs-toggle="tab" data-bs-target="#ad-interactions" type="button" role="tab">Ad Interactions ({{ $user->adInteractions->count() }})</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="products-tab" data-bs-toggle="tab" data-bs-target="#products" type="button" role="tab">Products ({{ $user->products->count() }})</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="courses-tab" data-bs-toggle="tab" data-bs-target="#courses" type="button" role="tab">Courses ({{ $user->courses->count() }})</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="enrollments-tab" data-bs-toggle="tab" data-bs-target="#enrollments" type="button" role="tab">Enrollments ({{ $user->courseEnrollments->count() }})</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="brain-teasers-tab" data-bs-toggle="tab" data-bs-target="#brain-teasers" type="button" role="tab">Brain Teasers ({{ $user->brainTeasers->count() }})</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="brain-attempts-tab" data-bs-toggle="tab" data-bs-target="#brain-attempts" type="button" role="tab">Brain Attempts ({{ $user->brainTeaserAttempts->count() }})</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="transactions-tab" data-bs-toggle="tab" data-bs-target="#transactions" type="button" role="tab">Transactions ({{ $user->transactions->count() }})</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="withdrawals-tab" data-bs-toggle="tab" data-bs-target="#withdrawals" type="button" role="tab">Withdrawals ({{ $user->withdrawalRequests->count() }})</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="access-keys-tab" data-bs-toggle="tab" data-bs-target="#access-keys" type="button" role="tab">Access Keys ({{ $user->createdAccessKeys->count() }})</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="sponsored-posts-tab" data-bs-toggle="tab" data-bs-target="#sponsored-posts" type="button" role="tab">Sponsored Posts ({{ $user->sponsoredPosts->count() }})</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="referrals-tab" data-bs-toggle="tab" data-bs-target="#referrals" type="button" role="tab">Referrals ({{ $user->referrals->count() }})</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="referred-by-tab" data-bs-toggle="tab" data-bs-target="#referred-by" type="button" role="tab">Referred By</button>
                        </li>
                    </ul>
                    
                    <!-- Tab panes -->
                    <div class="tab-content mt-3" id="userRelationshipsTabContent">
                        <!-- Projects -->
                        <div class="tab-pane fade show active" id="projects" role="tabpanel">
                            @if($user->projects->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Status</th>
                                                <th>Created</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($user->projects as $project)
                                            <tr>
                                                <td>{{ $project->id }}</td>
                                                <td>{{ $project->name }}</td>
                                                <td>{{ $project->status }}</td>
                                                <td>{{ $project->created_at->format('M d, Y') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">No projects found.</p>
                            @endif
                        </div>
                        
                        <!-- Images -->
                        <div class="tab-pane fade" id="images" role="tabpanel">
                            @if($user->images->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Status</th>
                                                <th>Created</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($user->images as $image)
                                            <tr>
                                                <td>{{ $image->id }}</td>
                                                <td>{{ $image->name }}</td>
                                                <td>{{ $image->status }}</td>
                                                <td>{{ $image->created_at->format('M d, Y') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">No images found.</p>
                            @endif
                        </div>
                        
                        <!-- Processing Jobs -->
                        <div class="tab-pane fade" id="processing-jobs" role="tabpanel">
                            @if($user->processingJobs->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Type</th>
                                                <th>Status</th>
                                                <th>Created</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($user->processingJobs as $job)
                                            <tr>
                                                <td>{{ $job->id }}</td>
                                                <td>{{ $job->type }}</td>
                                                <td>{{ $job->status }}</td>
                                                <td>{{ $job->created_at->format('M d, Y') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">No processing jobs found.</p>
                            @endif
                        </div>
                        
                        <!-- Tags -->
                        <div class="tab-pane fade" id="tags" role="tabpanel">
                            @if($user->tags->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Created</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($user->tags as $tag)
                                            <tr>
                                                <td>{{ $tag->id }}</td>
                                                <td>{{ $tag->name }}</td>
                                                <td>{{ $tag->created_at->format('M d, Y') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">No tags found.</p>
                            @endif
                        </div>
                        
                        <!-- Advertisements -->
                        <div class="tab-pane fade" id="ads" role="tabpanel">
                            @if($user->advertisements->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Title</th>
                                                <th>Status</th>
                                                <th>Budget</th>
                                                <th>Spent</th>
                                                <th>Created</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($user->advertisements as $ad)
                                            <tr>
                                                <td>{{ $ad->id }}</td>
                                                <td>{{ $ad->title }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $ad->status == 'active' ? 'success' : ($ad->status == 'pending' ? 'warning' : 'secondary') }}">
                                                        {{ ucfirst($ad->status) }}
                                                    </span>
                                                </td>
                                                <td>₦{{ number_format($ad->budget, 2) }}</td>
                                                <td>₦{{ number_format($ad->spent, 2) }}</td>
                                                <td>{{ $ad->created_at->format('M d, Y') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">No advertisements found.</p>
                            @endif
                        </div>
                        
                        <!-- Ad Interactions -->
                        <div class="tab-pane fade" id="ad-interactions" role="tabpanel">
                            @if($user->adInteractions->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Ad</th>
                                                <th>Type</th>
                                                <th>Reward</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($user->adInteractions as $interaction)
                                            <tr>
                                                <td>{{ $interaction->id }}</td>
                                                <td>{{ $interaction->advertisement->title ?? 'N/A' }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $interaction->type == 'view' ? 'primary' : 'success' }}">
                                                        {{ ucfirst($interaction->type) }}
                                                    </span>
                                                </td>
                                                <td>₦{{ number_format($interaction->reward_earned, 2) }}</td>
                                                <td>{{ $interaction->interacted_at->format('M d, Y H:i') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">No ad interactions found.</p>
                            @endif
                        </div>
                        
                        <!-- Products -->
                        <div class="tab-pane fade" id="products" role="tabpanel">
                            @if($user->products->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Price</th>
                                                <th>Status</th>
                                                <th>Created</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($user->products as $product)
                                            <tr>
                                                <td>{{ $product->id }}</td>
                                                <td>{{ $product->name }}</td>
                                                <td>₦{{ number_format($product->price, 2) }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $product->status == 'active' ? 'success' : 'secondary' }}">
                                                        {{ ucfirst($product->status) }}
                                                    </span>
                                                </td>
                                                <td>{{ $product->created_at->format('M d, Y') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">No products found.</p>
                            @endif
                        </div>
                        
                        <!-- Courses -->
                        <div class="tab-pane fade" id="courses" role="tabpanel">
                            @if($user->courses->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Title</th>
                                                <th>Price</th>
                                                <th>Status</th>
                                                <th>Created</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($user->courses as $course)
                                            <tr>
                                                <td>{{ $course->id }}</td>
                                                <td>{{ $course->title }}</td>
                                                <td>₦{{ number_format($course->price, 2) }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $course->status == 'published' ? 'success' : 'secondary' }}">
                                                        {{ ucfirst($course->status) }}
                                                    </span>
                                                </td>
                                                <td>{{ $course->created_at->format('M d, Y') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">No courses found.</p>
                            @endif
                        </div>
                        
                        <!-- Enrollments -->
                        <div class="tab-pane fade" id="enrollments" role="tabpanel">
                            @if($user->courseEnrollments->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Course</th>
                                                <th>Progress</th>
                                                <th>Enrolled</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($user->courseEnrollments as $enrollment)
                                            <tr>
                                                <td>{{ $enrollment->id }}</td>
                                                <td>{{ $enrollment->course->title ?? 'N/A' }}</td>
                                                <td>{{ $enrollment->progress }}%</td>
                                                <td>{{ $enrollment->created_at->format('M d, Y') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">No course enrollments found.</p>
                            @endif
                        </div>
                        
                        <!-- Brain Teasers -->
                        <div class="tab-pane fade" id="brain-teasers" role="tabpanel">
                            @if($user->brainTeasers->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Question</th>
                                                <th>Category</th>
                                                <th>Reward</th>
                                                <th>Created</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($user->brainTeasers as $brainTeaser)
                                            <tr>
                                                <td>{{ $brainTeaser->id }}</td>
                                                <td>{{ Str::limit($brainTeaser->question, 50) }}</td>
                                                <td>{{ $brainTeaser->category }}</td>
                                                <td>₦{{ number_format($brainTeaser->reward_amount, 2) }}</td>
                                                <td>{{ $brainTeaser->created_at->format('M d, Y') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">No brain teasers found.</p>
                            @endif
                        </div>
                        
                        <!-- Brain Attempts -->
                        <div class="tab-pane fade" id="brain-attempts" role="tabpanel">
                            @if($user->brainTeaserAttempts->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Brain Teaser</th>
                                                <th>Answer</th>
                                                <th>Correct</th>
                                                <th>Attempted</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($user->brainTeaserAttempts as $attempt)
                                            <tr>
                                                <td>{{ $attempt->id }}</td>
                                                <td>{{ Str::limit($attempt->brainTeaser->question ?? 'N/A', 30) }}</td>
                                                <td>{{ Str::limit($attempt->user_answer, 20) }}</td>
                                                <td>
                                                    @if($attempt->is_correct)
                                                        <span class="badge bg-success">Yes</span>
                                                    @else
                                                        <span class="badge bg-danger">No</span>
                                                    @endif
                                                </td>
                                                <td>{{ $attempt->created_at->format('M d, Y H:i') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">No brain teaser attempts found.</p>
                            @endif
                        </div>
                        
                        <!-- Transactions -->
                        <div class="tab-pane fade" id="transactions" role="tabpanel">
                            @if($user->transactions->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Type</th>
                                                <th>Amount</th>
                                                <th>Description</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($user->transactions as $transaction)
                                            <tr>
                                                <td>{{ $transaction->id }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $transaction->type == 'credit' ? 'success' : 'danger' }}">
                                                        {{ ucfirst($transaction->type) }}
                                                    </span>
                                                </td>
                                                <td>₦{{ number_format($transaction->amount, 2) }}</td>
                                                <td>{{ Str::limit($transaction->description, 30) }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $transaction->status == 'completed' ? 'success' : 'warning' }}">
                                                        {{ ucfirst($transaction->status) }}
                                                    </span>
                                                </td>
                                                <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">No transactions found.</p>
                            @endif
                        </div>
                        
                        <!-- Withdrawals -->
                        <div class="tab-pane fade" id="withdrawals" role="tabpanel">
                            @if($user->withdrawalRequests->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Amount</th>
                                                <th>Method</th>
                                                <th>Status</th>
                                                <th>Requested</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($user->withdrawalRequests as $withdrawal)
                                            <tr>
                                                <td>{{ $withdrawal->id }}</td>
                                                <td>₦{{ number_format($withdrawal->amount, 2) }}</td>
                                                <td>{{ $withdrawal->payment_method }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $withdrawal->status == 'completed' ? 'success' : ($withdrawal->status == 'pending' ? 'warning' : 'danger') }}">
                                                        {{ ucfirst($withdrawal->status) }}
                                                    </span>
                                                </td>
                                                <td>{{ $withdrawal->created_at->format('M d, Y H:i') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">No withdrawal requests found.</p>
                            @endif
                        </div>
                        
                        <!-- Access Keys -->
                        <div class="tab-pane fade" id="access-keys" role="tabpanel">
                            @if($user->createdAccessKeys->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Key</th>
                                                <th>Package</th>
                                                <th>Uses</th>
                                                <th>Created</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($user->createdAccessKeys as $key)
                                            <tr>
                                                <td>{{ $key->id }}</td>
                                                <td>{{ Str::limit($key->key, 15) }}</td>
                                                <td>{{ $key->package->name ?? 'N/A' }}</td>
                                                <td>{{ $key->used_count }} / {{ $key->max_uses }}</td>
                                                <td>{{ $key->created_at->format('M d, Y') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">No access keys created.</p>
                            @endif
                        </div>
                        
                        <!-- Sponsored Posts -->
                        <div class="tab-pane fade" id="sponsored-posts" role="tabpanel">
                            @if($user->sponsoredPosts->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Title</th>
                                                <th>Budget</th>
                                                <th>Spent</th>
                                                <th>Status</th>
                                                <th>Created</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($user->sponsoredPosts as $post)
                                            <tr>
                                                <td>{{ $post->id }}</td>
                                                <td>{{ Str::limit($post->title, 30) }}</td>
                                                <td>₦{{ number_format($post->budget, 2) }}</td>
                                                <td>₦{{ number_format($post->spent, 2) }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $post->status == 'active' ? 'success' : 'secondary' }}">
                                                        {{ ucfirst($post->status) }}
                                                    </span>
                                                </td>
                                                <td>{{ $post->created_at->format('M d, Y') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">No sponsored posts found.</p>
                            @endif
                        </div>
                        
                        <!-- Referrals -->
                        <div class="tab-pane fade" id="referrals" role="tabpanel">
                            @if($user->referrals->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Joined</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($user->referrals as $referral)
                                            <tr>
                                                <td>{{ $referral->id }}</td>
                                                <td>{{ $referral->name }}</td>
                                                <td>{{ $referral->email }}</td>
                                                <td>{{ $referral->created_at->format('M d, Y') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">No referrals found.</p>
                            @endif
                        </div>
                        
                        <!-- Referred By -->
                        <div class="tab-pane fade" id="referred-by" role="tabpanel">
                            @if($user->referredBy)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Joined</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>{{ $user->referredBy->id }}</td>
                                                <td>{{ $user->referredBy->name }}</td>
                                                <td>{{ $user->referredBy->email }}</td>
                                                <td>{{ $user->referredBy->created_at->format('M d, Y') }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">This user was not referred by anyone.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">User Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-primary text-white rounded">
                                <h4>{{ $stats['total_transactions'] }}</h4>
                                <p class="mb-0">Transactions</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-success text-white rounded">
                                <h4>₦{{ number_format($stats['total_earned'], 2) }}</h4>
                                <p class="mb-0">Total Earned</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-danger text-white rounded">
                                <h4>₦{{ number_format($stats['total_spent'], 2) }}</h4>
                                <p class="mb-0">Total Spent</p>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-info text-white rounded">
                                <h4>₦{{ number_format($stats['normal_earnings'], 2) }}</h4>
                                <p class="mb-0">Normal Earnings</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-success text-white rounded">
                                <h4>₦{{ number_format($stats['referral_earnings'], 2) }}</h4>
                                <p class="mb-0">Referral Earnings</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-info text-white rounded">
                                <h4>₦{{ number_format($stats['total_earnings'], 2) }}</h4>
                                <p class="mb-0">Total Earnings</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Profile Image</h5>
                </div>
                <div class="card-body text-center">
                    @if($user->profile_image)
                        <img src="{{ Storage::url($user->profile_image) }}" alt="{{ $user->name }}" class="img-fluid rounded">
                    @else
                        <div class="bg-light p-5 rounded">
                            <i class="fas fa-user fa-3x text-muted"></i>
                            <p class="mt-2">No profile image</p>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        <small>Withdrawal access is controlled globally in <a href="{{ route('admin.settings.index') }}#financial">Financial Settings</a>.</small>
                    </p>
                    
                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm w-100">Delete User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection