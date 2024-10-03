@extends('allposts.app')

@section('title', 'All Posts')

@section('content')
    <div class="container">
        <h1>All Posts</h1>
        <a href="#" id="createPostBtn" class="btn btn-primary mb-3">Create Post</a>
        <div id="successMessage" class="alert alert-success d-none"></div>
        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="postsTableBody">
                @foreach ($posts as $post)
                    <tr id="post-{{ $post->id }}">
                        <td>{{ $post->title }}</td>
                        <td>{{ Str::limit($post->description, 50) }}</td>
                        <td>{{ $post->post_type }}</td>
                        <td>
                            <button class="btn btn-warning editPostBtn" data-id="{{ $post->id }}">Edit</button>
                            <button class="btn btn-danger deletePostBtn" data-id="{{ $post->id }}">Delete</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Create Post Modal -->
    <div class="modal" id="createPostModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="createPostForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Create Post</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" name="description" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="post_type">Type</label>
                            <select class="form-control" name="post_type" required>
                                <option value="Education">Education</option>
                                <option value="Career">Career</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="image">Image</label>
                            <input type="file" class="form-control" name="image">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Create Post</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Post Modal -->
    <div class="modal" id="editPostModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editPostForm" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="post_id" id="editPostId">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Post</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="editTitle">Title</label>
                            <input type="text" class="form-control" id="editTitle" name="title" required>
                        </div>
                        <div class="form-group">
                            <label for="editDescription">Description</label>
                            <textarea class="form-control" id="editDescription" name="description" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="editPostType">Type</label>
                            <select class="form-control" id="editPostType" name="post_type" required>
                                <option value="Education">Education</option>
                                <option value="Career">Career</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="editImage">Image</label>
                            <input type="file" class="form-control" name="image">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Update Post</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Create Post
        $('#createPostBtn').click(function() {
            $('#createPostModal').modal('show');
        });

        $('#createPostForm').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: '{{ route("posts.store") }}',
                method: 'POST',
                data: new FormData(this),
                contentType: false,
                processData: false,
                success: function(response) {
                    $('#createPostModal').modal('hide');
                    $('#successMessage').removeClass('d-none').text(response.success);
                    $('#postsTableBody').append(`
                        <tr id="post-${response.post.id}">
                            <td>${response.post.title}</td>
                            <td>${response.post.description}</td>
                            <td>${response.post.post_type}</td>
                            <td>
                                <button class="btn btn-warning editPostBtn" data-id="${response.post.id}">Edit</button>
                                <button class="btn btn-danger deletePostBtn" data-id="${response.post.id}">Delete</button>
                            </td>
                        </tr>
                    `);
                },
                error: function(err) {
                    // Handle errors
                }
            });
        });

        // Edit Post
        $(document).on('click', '.editPostBtn', function() {
            const postId = $(this).data('id');
            $.get(`/posts/${postId}/edit`, function(data) {
                $('#editPostId').val(postId);
                $('#editTitle').val(data.title);
                $('#editDescription').val(data.description);
                $('#editPostType').val(data.post_type);
                $('#editPostModal').modal('show');
            });
        });

        $('#editPostForm').submit(function(e) {
            e.preventDefault();
            const postId = $('#editPostId').val();
            $.ajax({
                url: `/posts/${postId}`,
                method: 'PUT',
                data: new FormData(this),
                contentType: false,
                processData: false,
                success: function(response) {
                    $('#editPostModal').modal('hide');
                    $(`#post-${postId} td:nth-child(1)`).text(response.post.title);
                    $(`#post-${postId} td:nth-child(2)`).text(response.post.description);
                    $(`#post-${postId} td:nth-child(3)`).text(response.post.post_type);
                    $('#successMessage').removeClass('d-none').text(response.success);
                },
                error: function(err) {
                    // Handle errors
                }
            });
        });

        // Delete Post
        $(document).on('click', '.deletePostBtn', function() {
            const postId = $(this).data('id');
            if (confirm('Are you sure you want to delete this post?')) {
                $.ajax({
                    url: `/posts/${postId}`,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $(`#post-${postId}`).remove();
                        $('#successMessage').removeClass('d-none').text(response.success);
                    },
                    error: function(err) {
                        // Handle errors
                    }
                });
            }
        });
    });
</script>
@endsection
