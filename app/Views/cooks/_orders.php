<?php if(empty($orders)):?>
    <p>Активных блюд нет</p>
<?php else:?>
    <?php foreach($orders as $orderId=>$order):?>
        <section>
            <h2>Заказ №<?=$orderId?></h2>
            <p>Стол:<?=$order['tableNumber'] ? '№' . (int)$order['tableNumber'] : 'Не указан'?></p>
            <p>Клиент:<?=htmlspecialchars($order['userName'], ENT_QUOTES, 'UTF-8')?></p>
            <p>Создан:<?=htmlspecialchars($order['created'], ENT_QUOTES, 'UTF-8')?></p>

            <?php foreach($order['items'] as $item):?>
                <div data-order-item="<?=(int)$item['orderItemId']?>" data-status="<?=htmlspecialchars($item['statusCode'], ENT_QUOTES, 'UTF-8')?>">
                    <img src="/Cafe/public/uploads/dish/<?=htmlspecialchars($item['photo'], ENT_QUOTES, 'UTF-8')?>" alt="<?=htmlspecialchars($item['dishName'], ENT_QUOTES, 'UTF-8')?>" width="150">
                    <h3><?=htmlspecialchars($item['dishName'], ENT_QUOTES, 'UTF-8')?></h3>
                    <p>Категория:<?=htmlspecialchars($item['categoryName'], ENT_QUOTES, 'UTF-8')?></p>
                    <p>Количество:<?=(int)$item['quantity']?></p>
                    <p>Комментарий:<?=htmlspecialchars($item['comment'] ?: 'Нет', ENT_QUOTES, 'UTF-8')?></p>
                    <p>Время приготовления:<?=(int)$item['cooktime']?> минут</p>
                    <p>Статус:<?=htmlspecialchars($item['statusName'], ENT_QUOTES, 'UTF-8')?></p>

                    <?php if($item['statusCode']==='new'):?>
                        <form class="ajax-order-status" action="/Cafe/app/Controlers/CookController.php?action=updateItemStatus" method="post">
                            <?=Csrf::input()?>
                            <input type="hidden" name="orderItemId" value="<?=(int)$item['orderItemId']?>">
                            <input type="hidden" name="nextStatusCode" value="cooking">
                            <button type="submit">Начать готовить</button>
                        </form>
                    <?php elseif($item['statusCode']==='cooking'):?>
                        <form class="ajax-order-status" action="/Cafe/app/Controlers/CookController.php?action=updateItemStatus" method="post">
                            <?=Csrf::input()?>
                            <input type="hidden" name="orderItemId" value="<?=(int)$item['orderItemId']?>">
                            <input type="hidden" name="nextStatusCode" value="ready">
                            <button type="submit">Блюдо готово</button>
                        </form>
                    <?php elseif($item['statusCode']==='ready'):?>
                        <p>Ожидает официанта</p>
                    <?php endif;?>
                </div>

                <hr>
            <?php endforeach;?>
        </section>
    <?php endforeach;?>
<?php endif;?>
