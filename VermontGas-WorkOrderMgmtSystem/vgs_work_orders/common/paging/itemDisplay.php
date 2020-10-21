<?php
// Call this with itemNo added to query string at end of URL, like:
// .../itemDisplay.php?itemNo=item_0001
$product = $_GET['itemNo'];
$imgUrl = "http://corvetteamerica.com/images/catalog/products/$product.jpg";
?>
<h1>Product Image</h1>
<hr>
<img src="<?= $imgUrl ?>" alt="Image for Item No. <?= $product ?>">
<hr>