<?php
include 'auth.php';
checkRole('admin');
include 'config.php';

// Update Content
if ($_SERVER["REQUEST_METHOD"] == "POST") {
   $page_name = $_POST['page_name'];
   $content = $_POST['content'];

    $conn->query("UPDATE pages SET content='$content' WHERE page_name='$page_name'");
    echo "Page updated successfully!";
}

// Fetch Pages
$pages = $conn->query("SELECT * FROM pages");
?>
<h2>Edit Pages</h2>
<form method="post">
    <select name="page_name">
        <?php while ($row = $pages->fetch_assoc()) { ?>
           <option value="<?php echo $row['page_name']; ?>"><?php echo ucfirst($row['page_name']); ?></option>
           <?php } ?>
    </select><br>
    <textarea name="content" rows="5" cols="50"></textarea><br>
   <button type="submit">Update Page</button>
</form>
