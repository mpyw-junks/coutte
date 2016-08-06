<form method="post" action="" enctype="multipart/form-data">
    <input type="file" name="file[tmp_name][y]">
    <input type="submit" value="submit">
</form>
<?php if (isset($_FILES['file']['error']['tmp_name']['y']) && is_int($_FILES['file']['error']['tmp_name']['y'])): ?>
<?php if ($_FILES['file']['error']['tmp_name']['y'] === UPLOAD_ERR_OK): ?>
<div id="success">SUCCESS</div>
<?php else: ?>
<div id="error">ERROR</div>
<?php endif; ?>
<?php endif; ?>
