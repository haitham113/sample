<form action="/test" method='POST' enctype="multipart/form-data">
    @csrf
    <input type="file" name='file'>
    <input type="submit" name='sub'>
</form>