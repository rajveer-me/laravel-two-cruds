<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Add post</title>
</head>
<body>
    <form id="addform">
        <input type="text" id="title" placeholder="title"/>
        <textarea id="description" placeholder="description"></textarea>
        <input type="file" id="image"/>
        <input type="submit" id="addPost">
    </form>
<script>
    var addform = document.querySelector("#addform");

    addform.onsubmit = async (e) => {
        e.preventDefault();

        const title = document.querySelector("#title").value;
        const description = document.querySelector('#description').value;
        const image = document.querySelector("#image").files[0];

        var formData = new FormData(); 
        formData.append('title', title);
        formData.append('description', description);
        formData.append('image', image);

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        let response = await fetch('/posts', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken, // CSRF token for Laravel
                'Authorization': `Bearer ${localStorage.getItem('api_token')}`
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log(data.message);
                window.location.href = "/allposts";
            } else {
                console.error("Error:", data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

</script>
</body>
</html>
