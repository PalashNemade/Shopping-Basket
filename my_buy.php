<?php
// Start Session
session_start();
error_reporting(0);
?>

<?php
class Basket
{
    public $productId, $productName, $productImage, $productMinPrice, $productURL;

    public function __construct($productId, $productName, $productImage, $productMinPrice, $productURL)
    {
        $this->productId = $productId;
        $this->productName = $productName;
        $this->productImage = $productImage;
        $this->productURL = $productURL;
        $this->productMinPrice = $productMinPrice;
    }
    function getProductId(){
        return $this->productId;
    }
    function getProductName(){
        return $this->productName;
    }
    function getProductImage(){
        return $this->productImage;
    }
    function getProductURL(){
        return $this->productURL;
    }
    function getProductMinPrice(){
        return $this->productMinPrice;
    }
}
?>
<html>
<head>
    <title>Buy Products</title>
</head>
<h1 align="center">
    Shopping Basket
</h1>
<table border="1" id="basket">
    <?php
    if(isset($_GET['buy'])) {
        foreach ($_SESSION['obj'] as $product) {
            if($_GET['buy']==$product['productId']) {
                $_SESSION['item'][$_GET['buy']] = $product;
            }
        }

    }

    if(isset($_GET['clear'])){
        unset($_SESSION['item']);
        $_SESSION['total'] = 0;
    }

    if(isset($_GET['delete'])){
        //print_r($_SESSION['item']);
        $deleteId = $_GET['delete'];
        //print_r($deleteId);
        //print_r($_SESSION['item'][$deleteId]['productMinPrice']);
        $_SESSION['total'] -= $_SESSION['item'][$deleteId]['productMinPrice'];
        //print_r($_SESSION['total']);
        unset($_SESSION['item'][$deleteId]);
    }

    $_SESSION['total'] = 0;


    foreach($_SESSION['item'] as $basketItem ) {
        echo "<tr>";
        echo "<td><a href = '" . $basketItem['productURL'] . "'><img src = '" . $basketItem['productImage'] . "' /></a></td>";
        echo "<td>" . $basketItem['productName'] . "</td>";
        echo "<td>" . $basketItem['productMinPrice'] . "</td>";
        echo "<td><a href = 'my_buy.php?delete=".$basketItem['productId']."' >Delete</a>";
        echo "</tr>";
        $_SESSION['total'] = $_SESSION['total'] + $basketItem['productMinPrice'];
    //}
    }

    ?>
</table>
<label name = "total" >
    Total :$<?php echo $_SESSION['total']; ?>
</label>
<br>
<form action = "my_buy.php" method = "GET">
    <?php
        ?>
    <input type = "submit" value = "Empty Basket" name = "clear">
</form>
<form action = "my_buy.php" method= "get">
    <fieldset>
        <legend>Find Products:</legend>
        <label>Category: <select name = "category" >
<?php
/**
 * Created by PhpStorm.
 * User: Palash
 */
error_reporting(E_ALL);
ini_set('display_errors','On');
$xmlstr = file_get_contents('http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/CategoryTree?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&visitorUserAgent&visitorIPAddress&trackingId=7000610&categoryId=72&showAllDescendants=true');
$xml = new SimpleXMLElement($xmlstr);
foreach($xml->children() as $component){
    if($component->getName() == "category") {
        echo "<b><optgroup label ='" .$component->name. "'></b>";
        foreach($component->children() as $subComponent){
            foreach($subComponent->children() as $subComponents){
                echo "<option>" .$subComponents->name. "</option>";
                if($subComponents->getName() == "category"){
                    echo "<b><optgroup label = '" .$subComponents->name. "'></b>";
                    foreach ($subComponents->children() as $item) {
                        foreach($item->children() as $items ){
                            if($items->getName() == "category"){
                                echo "<option value ='" .$items['id']. "'>" .$items->name. "</option><br />";
                            }
                        }
                    }
                    echo "</optgroup>";
                }

            }
        }
        echo "</optgroup>";
    }
}
?>
            </select>
        </label>
        <label>Search Keywords: <input type="text" name="search" />
        </label>
        <input type = "submit" value = "Search" />
</fieldset>
</form>
<table border = 1>
<?php

if(isset($_GET['category'])&&isset($_GET['search'])) {
    //(!(empty($_GET['search']))&& isset($_GET['search']))
    if(isset($_SESSION['obj'])) {
        unset($_SESSION['obj']);
} else {
        $_SESSION['obj'] = array();
    }
    $search = $_GET['search'];
    $categoryId = $_GET['category'];
    $xmlOutput = file_get_contents('http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/GeneralSearch?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&trackingId=7000610&categoryId=' . $categoryId . '&keyword=' . $search . '&numItems=20');
    $xmlObj = new SimpleXMLElement($xmlOutput);
    if (!isset($_SESSION['item'])) {
        $_SESSION['item'] = array();
    }

#print_r($xmlObj);
    foreach($xmlObj->children() as $categories) {
        if ($categories->getname() == "categories") {
            foreach ($categories->children() as $category) {
                if ($category->getName() == "category") {
                    foreach ($category->children() as $items) {
                        if ($items->getName() == "items")
                            foreach ($items->children() as $product) {
                                if ($product->getName() == "product") {
                                    $id = (string)$product['id'];
                                    $name = (string)$product->name;
                                    $image = (string)$product->images->image->sourceURL;
                                    $price = (float)$product->minPrice;
                                    $details = (string)$product->productSpecsURL;

                                    echo "<tr border = '1'>";
                                    echo "<td><a href='my_buy.php?buy=" .$id. "'><img src = '" . $product->images->image->sourceURL . "'></a></td>";
                                    echo "<td>" . $product->name . "</td>";
                                    echo "<td><p>$" . $product->minPrice . "</p></td>";
                                    echo "<td>" . $product->fullDescription . "</td>";
                                    echo "</tr>";

                                    $buy = new Basket($id, $name, $image, $price, $details);
                                    $_SESSION['obj'][] = (array)$buy;
                                    //print_r($id);

                                    /* if (isset($_GET['buy'])) {
                                        echo $_GET['buy'];
                                        $_SESSION['item'][$_GET['buy']] = (array)$buy;
                                    } */
                                }
                            }
                    }
                }
            }
        }
    }
}
//print_r($_SESSION['obj']);
?>
</table>

</body>
</html>