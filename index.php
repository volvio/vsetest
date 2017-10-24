<?php
require 'class/ModelProduct.php';
require 'class/ModelPromotion.php';
require 'class/ModelSite.php';
require 'class/DateBase.php';
require 'class/Validate.php';

$uploadfile = $_SERVER["DOCUMENT_ROOT"] . "/files/upload.xml";
$error = "";
$site_url = "http://site.com/";//сайт для которго выполняется загрузка данных

$db = DateBase::getDB();//подключение к базе данных


$site = new ModelSite($db);//список сайтов
$promotion = new ModelPromotion($db);//список акций
$product = new ModelProduct($db);//список продуктов
$validateclass = new Validate();//правила проверки

$site_id = $site->getSiteIdByUrl($site_url);

$validate = 0;//флаг проверки = 1 или сохранения данных = 0
$message = "";//Сообщение об успешной загрузке данных
$promotiont_added = 0;
$promotiont_updated = 0;
$product_added = 0;
$product_updated = 0;
$proms_validate = array();

if (!$site_id) {

    $site->addSite($site_url);
    $site_id = $site->addSite($site_url);
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST["onlyvalidate"]) && $_POST["onlyvalidate"] > 0) {
        $validate = 1;//устанавливаем флаг выполнить только проверку
    }
    if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
        $xml_content = file_get_contents($uploadfile);
        if (strpos($xml_content, '<?xml version="1.0" encoding="utf-8"?>') !== FALSE) {//проверка на xml формат
            $xml = new SimpleXMLElement($xml_content);

            if (isset($xml->sale)) {//проверка на наличие тега sale 
                $sales = $xml->sale;
                $j = 0;
                foreach ($sales as $key => $sale) {//обработка всех акций
                    $j++;
                    if ($validateclass->validateAll($sale, $j)){//проверка параметров
                        //если поля не указаны
                        $description = "";
                        if (isset($sale->description)){
                            $description = (string) $sale->description[0];
                        }
                        $url = "";
                        if(isset($sale->url)){
                           $url = (string)$sale->url[0]; 
                        }
                        $image = "";
                        if(isset($sale->image)){
                           $image = (string)$sale->image[0]; 
                        }
                        //массив для сохранения акций
                        $promition_arr = [
                            "id" => 0,
                            "title" => (string) $sale->title[0],
                            "description" => $description,
                            "url" => (string) $url,
                            "image" => (string) $image,
                            "date_start" => (string) $sale->date_start[0],
                            "date_end" => (string) $sale->date_end[0]
                        ];
                        //Обработка акций
                        $products = $sale->products[0];
                        $promID = $promotion->findPromotion($promition_arr["title"], $promition_arr["description"]);
                        if ($promID > 0) {
                            $promition_arr["id"] = $promID;
                            if ($validate == 0) {
                                $promotion->updatePromotion($promition_arr);//обновление существующей
                            }
                            $promotiont_updated++;
                        } else {
                            if ($validate == 0) {
                                $promID = $promotion->addPromotion($promition_arr);//добавление новой
                            }
                            $promotiont_added++;
                        }
                        //обработка продуктов
                        foreach ($products as $prod) {

                            $prod_id = (string) $prod["id"];
                            $prod_url = (string) $prod;

                            if ($product->getProduct($prod_id, $site_id)) {
                                if ($validate == 0) {
                                    $product->updateProduct($prod_id, $site_id, $prod_url);//обновление
                                }
                                $product_updated++;
                            } else {
                                if ($validate == 0) {
                                    $product->addProduct($prod_id, $site_id, $prod_url);//добавление
                                }
                                $product_added ++;
                            }
                            //связь продуктов с акциями
                            if ($validate == 0) {
                                $promotion->delProductToPromotion($prod_id, $promID, $site_id);//убираем все связи товары акции
                                $promotion->addProductToPromotion($prod_id, $promID, $site_id);//создаем новые записи товары акции
                            }
                        }
                    $proms_validate[] = $promition_arr;
                    }else{
                        $e = $validateclass->getError();
                        if ($e){
                            foreach ($e as $value) {
                               $error[] = $value; 
                            }
                        }
                        $validateclass->clearError();
                    }
                }
            } else {
                $error[] = "Неверный формат файла";
            }
        } else {
            $error[] = "Неверный формат файла";
        }
        $message[] = "Акций обновлено:$promotiont_updated ; Акций добавлено:$promotiont_added; Продуктов обновлено:$product_updated; Продуктов добавлено:$product_added";
    }
}
$promotions = $promotion->getPromotions(0, 500);
if ($validate == 1 && is_array($proms_validate) && count($proms_validate) > 0) {
    $promotions = $proms_validate;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Загрузка акций</title>
        <meta charset="UTF-8">
        <style>
            table{
                border-collapse: collapse;
            }
            table td,table th{
                border: 1px solid #333;
            }
            .error{
                color:red;
            }
            .message{
                color:blue;
            }
        </style>
    </head>
    <body>
        <h1>Загрузка файла с акциями формат xml </h1>
        <h3 class="error"><?php
if (is_array($error) && count($error) > 0)
    foreach ($error as $err) {
        echo $err . "<br>";
    }
?></h3>
        <h3 class="message"><?php
if (is_array($message) && count($message) > 0)
    foreach ($message as $m) {
        echo $m . "<br>";
    }
?></h3>
        <div>
            <form id="form" action="index.php" method="POST" enctype="multipart/form-data">
                <fieldset style="width: 600px">
                    <input type="hidden" name="MAX_FILE_SIZE" value="3000000" />
                    <input type="hidden" id="onlyvalidate" name="onlyvalidate" value="0" />
                    <p><input name="file" type="file" /></p>
                    <p><input type="button" name="check" onclick="validatefile()" id="check" value="Проверить"/>
                        <input type="button" onclick="savefile()" value="Загрузить" /></p>
                </fieldset>
            </form>
            <script>
                function validatefile() {
                    var form = document.getElementById('form');
                    var onlyvalidate = document.getElementById('onlyvalidate')
                    onlyvalidate.value = 1;
                    form.submit();
                }
                function savefile() {
                    var form = document.getElementById('form');
                    var onlyvalidate = document.getElementById('onlyvalidate')
                    onlyvalidate.value = 0;
                    form.submit();
                }
            </script>
        </div>
        <?php if ($validate == 0) { ?>
            <h2>Загруженные акции</h2>
        <?php } else { ?>
            <h2>Проверенные акции</h2>
<?php } ?>
        <table>
            <thead>
                <tr>
                    <th>№</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Date Start</th>
                    <th>Date End</th>
                    <th>Количество товаров </th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (isset($promotions)) {
                    $i = 0;
                    foreach ($promotions as $key => $prom) {
                        $i++;
                        $promId = $prom["id"];
                        $productcount = $promotion->getCountProductToPromotion($promId);
                        ?>
                        <tr>
                            <td><?php echo $i; ?></td>
                            <td><?php echo $prom["title"]; ?></td>
                            <td><?php echo $prom["description"]; ?></td>
                            <td><?php echo $prom["date_start"]; ?></td>
                            <td><?php echo $prom["date_end"]; ?></td>
                            <td><?php echo $productcount; ?></td>
                        </tr>   
                        <?php
                    }
                }
                ?>
            </tbody>
        </table>
    </body>
</html>
