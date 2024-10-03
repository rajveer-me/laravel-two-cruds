<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Posts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>

    <div class="container mt-4">
        <div class="d-flex justify-content-between">
            <a href="/addpost" class="btn btn-primary">Add New Post</a>
            <button class="btn btn-danger" id="logoutButton">Logout</button>
        </div>

        <div id="postsContainer" class="mt-4"></div>
    </div>

    <!-- View Single Post Modal -->
    <div class="modal fade" id="singlepost" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Post Details</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="singlepostModalBody">
                    <!-- Post details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Post Modal -->
    <div class="modal fade" id="updatepost" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="updatePostLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="updatePostLabel">Update Post</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="updateForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="text" id="updateId" placeholder="Id" readonly class="form-control mb-2"/>
                        <input type="text" id="updateTitle" placeholder="Title" class="form-control mb-2"/>
                        <textarea id="updateDescription" placeholder="Description" class="form-control mb-2"></textarea>
                        <input type="file" id="updateImage" class="form-control mb-2"/>
                        <img id="showImage" src="" alt="Current Image" width="150px" class="mb-2"/>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <input type="submit" id="updatebutton" class="btn btn-primary" value="Update Post">
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <script>
        // Logout button click
        document.querySelector("#logoutButton").addEventListener('click', function () {
            const token = localStorage.getItem('api_token');

            fetch('/logout', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            })
                .then(response => response.json())
                .then(data => {
                    console.log(data);
                    window.location.href = "/login";
                });
        });

        // Load all posts data
        function loadData() {
            const token = localStorage.getItem('api_token');

            fetch('/posts', {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            })
                .then(response => response.json())
                .then(data => {
                    console.log(data.data.posts);
                    const allPosts = data.data.posts;
                    const postContainer = document.querySelector("#postsContainer");

                    let tableForm = `
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Image</th>
                                    <th>View</th>
                                    <th>Update</th>
                                    <th>Delete</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;

                    // Loop through posts
                    allPosts.forEach(post => {
                        tableForm += `
                            <tr>
                                <td>${post.id}</td>
                                <td>${post.title}</td>
                                <td>${post.description}</td>
                                <td><img src="/uploads/${post.image}" width="150px" /></td>
                                <td>
                                    <button type="button" class="btn btn-primary" data-bs-post="${post.id}" data-bs-toggle="modal" data-bs-target="#singlepost">
                                        View
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-warning" data-bs-post="${post.id}" data-bs-toggle="modal" data-bs-target="#updatepost">
                                        Update
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger" onclick="delete_post(${post.id})">Delete</button>
                                </td>
                            </tr>
                        `;
                    });

                    tableForm += `</tbody></table>`;
                    postContainer.innerHTML = tableForm;
                });
        }
        loadData();

        // View post in modal
        const singlePostModal = document.querySelector("#singlepost");
        if (singlePostModal) {
            singlePostModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const postId = button.getAttribute('data-bs-post');
                const modalBody = document.querySelector("#singlepost .modal-body");

                const token = localStorage.getItem('api_token');
                fetch(`/posts/${postId}`, {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-type': 'application/json'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        const post = data.data;
                        modalBody.innerHTML = `
                            <p><strong>Title:</strong> ${post.title}</p>
                            <p><strong>Description:</strong> ${post.description}</p>
                            <p><strong>Image:</strong> <img src="/uploads/${post.image}" width="150px" /></p>
                        `;
                    });
            });
        }

        // Open Update Modal and Pre-fill Data
        const updatePostModal = document.querySelector("#updatepost");
        if (updatePostModal) {
            updatePostModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const postId = button.getAttribute('data-bs-post');

                const token = localStorage.getItem('api_token');
                fetch(`/posts/${postId}`, {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        const post = data.data;

                        document.querySelector("#updateId").value = post.id;
                        document.querySelector("#updateTitle").value = post.title;
                        document.querySelector("#updateDescription").value = post.description;
                        document.querySelector("#showImage").src = `/uploads/${post.image}`;
                    });
            });
        }

        // Handle Update Post form submission
        // Handle Update Post form submission
const updateForm = document.querySelector("#updateForm");

updateForm.onsubmit = async (e) => {
    e.preventDefault();
    const token = localStorage.getItem('api_token');
    const postId = document.querySelector("#updateId").value;
    const title = document.querySelector("#updateTitle").value;
    const description = document.querySelector('#updateDescription').value;

    let formData = new FormData();
    formData.append('title', title);
    formData.append('description', description);

    let image = document.querySelector("#updateImage").files[0];
    if (image) {
        formData.append('image', image);
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    let response = await fetch(`/posts/${postId}`, {
        method: 'POST',
        body: formData,
        headers: {
            'Authorization': `Bearer ${token}`,
            'X-HTTP-Method-Override': 'PUT',  // Ensure PUT is used for updates
            'X-CSRF-TOKEN': csrfToken  // Add the CSRF token here
        }
    });

    if (response.ok) {
        alert("Post updated successfully!");
        loadData();
        updateForm.reset();
        const modal = bootstrap.Modal.getInstance(updatePostModal);
        modal.hide();
    } else {
        alert("Failed to update post");
    }
};


        // Delete Post Function
        function delete_post(id) {
            const token = localStorage.getItem('api_token');
            if (confirm('Are you sure you want to delete this post?')) {
                fetch(`/posts/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        alert("Post deleted successfully!");
                        loadData();
                    });
            }
        }
    </script>
</body>
</html>
