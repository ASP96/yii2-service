<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use pistol88\worksess\widgets\SessionGraph;
use nex\datepicker\DatePicker;

$this->title = 'Отчеты по услугам';
$this->params['breadcrumbs'][] = $this->title;

\pistol88\service\assets\BackendAsset::register($this);
?>
<div class="report-index">

    <div class="service-menu">
        <?=$this->render('../parts/menu');?>
    </div>

    <br style="clear: both;" />

    <div class="row session-finder">
        <div class="col-md-6">
            <form action="" method="get">
                <p>Выберите сессию:</p>
                <?= DatePicker::widget([
                    'name' => 'date',
                    'addon' => false,
                    'value' => $date,
                    'size' => 'sm',
                    'language' => 'ru',
                    'options' => [
                        'class' => 'get-sessions-by-date',
                        'href' => Url::toRoute(['/service/report/get-sessions']),
                    ],
                    'placeholder' => yii::t('order', 'Date from'),
                    'clientOptions' => [
                        'format' => 'L',
                        'minDate' => '2015-01-01',
                        'maxDate' => date('Y-m-d'),
                    ],
                    'dropdownItems' => [
                        ['label' => 'Yesterday', 'url' => '#', 'value' => \Yii::$app->formatter->asDate('-1 day')],
                        ['label' => 'Tomorrow', 'url' => '#', 'value' => \Yii::$app->formatter->asDate('+1 day')],
                        ['label' => 'Some value', 'url' => '#', 'value' => 'Special value'],
                    ],
                ]);?>

                <ul>
                    <?php foreach($sessions as $sessionList) { ?>
                    <li><a <?php if($session && $sessionList->id == $sessionId) echo 'style="font-weight: bold;"'; ?> href="<?=Url::toRoute(['/service/report/index', 'sessionId' => $sessionList->id]);?>"><?=date('d.m.Y H:i:s', $sessionList->start_timestamp);?> <?=$sessionList->shiftName;?> <?php if(isset($sessionList->user)) { ?> (<?=$sessionList->user->name;?>)<?php } ?></a></li>
                    <?php } ?>
                </ul>
            </form>

        </div>
        <div class="col-md-6">

        </div>
    </div>

<?php if($session) { ?>
    <div id="report-to-print">
        <h1>
            <?php if(isset($session->user)) { ?>Администратор <?=$session->user->name;?><?php } ?>
            
            <?php if(yii::$app->has('organization') && $organization = yii::$app->organization->get()) { ?>
                (<?=$organization->name;?>)
            <?php } ?>
        </h1>
        <a href="#" class="btn btn-submit" onclick="pistol88.service.callPrint('report-to-print'); return false;" style="float: right;"><i class="glyphicon glyphicon-print"></i></a>
        <p><strong>Смена</strong>: <?=$session->shiftName;?></p>
        <p><strong>Старт</strong>: <?=date('d.m.Y H:i:s', $session->start_timestamp);?></p>
        <p><strong>Стоп</strong>: <?php if($session->stop_timestamp) echo date('d.m.Y H:i:s', $session->stop_timestamp); else echo '-';?></p>
        <p><strong>Продолжительность</strong>: <?=$session->getDuration();?></p>
        <hr style="clear: both;" />

        <h2>Заказы</h2>

        <?php $i = 0; $oi = 0; foreach($data['orders'] as $start => $group) { $i++; ?>
            <div class="row">
                <div class="col-md-7 report-services">
                    <table class="table services-list">
                        <?php if($i == 1) { ?>
                            <tr>
                                <th>Заказ</th>
                                <th>Услуги</th>
                                <th>Клиент</th>
                                <th>Промокод</th>
                                <th>Цена</th>
                                <th>Цена %</th>
                                <th>В базу</th>
                            </tr>
                        <?php } ?>

                        <?php foreach($group['orders'] as $order) { $oi++; ?>
                            <tr>
                                <td><?=$oi;?>. [<?=date('H:i', $order['timestamp']);?>] <a href="<?=Url::toRoute(['/order/order/view', 'id' => $order['id']]);?>"><i class="glyphicon glyphicon-eye-open"></i></a></td>
                                <td>
                                    <ul>
                                        <?php
                                        foreach($order['elements'] as $element) {
                                            echo "<li>{$element['serviceName']} - {$element['price']} {$currency}x{$element['count']}</li>";
                                        }
                                        ?>
                                    </ul>
                                </td>
                                <td>
                                    <?php if($order['user_id']) { ?>
                                        <a href="<?=Url::toRoute(['/client/client/view', 'id' => $order['user_id']]);?>"><?=$order['client_name'];?></a>
                                    <?php } else { ?>
                                        <?=$order['client_name'];?>
                                    <?php } ?>
                                </td>
                                <td>
                                    <?=$order['promocode'];?> 
                                    
                                </td>
                                <td>
                                    <?=$order['base_price']?> <?=$currency;?>
                                </td>
                                <td>
                                    <a href="<?=Url::toRoute(['/cashbox/operation/index', 'OperationSearch' => ['item_id' => $order['id'], 'model' => 'pistol88\order\models\Order']]);?>"><?=$order['price']?> <?=$currency;?></a>
                                    <br />
                                    <small><?=$order['payment_type_name'];?></small> 
                                </td>
                                <td <?php if($order['to_base'] != $order['price']) echo ' style="color: red;"'; ?>>
                                    <?=$order['to_base']?> <?=$currency;?>
                                </td>
                            </tr>
                        <?php } ?>
                    </table>
                    
                </div>
                <div class="col-md-1">
                    <p>Итого: <strong><?=$group['sum'];?></strong> <?=$currency;?></p>
                </div>
                <div class="col-md-4">
                    <div style="max-height: 250px; overflow-y: scroll;">
                        <p><strong><?=round($group['base'], 2);?> <?=$currency;?> (<?=$group['persent'];?>%)</strong>, делится между <strong><?=$group['workersCount'];?></strong> сотрудниками.</p>
                        
                        <table class="table">
                            <?php foreach($group['workers'] as $worker) { ?>
                                <tr>
                                    <td><a href="<?=Url::toRoute(['/staffer/staffer/view', 'id' => $worker['id']]);?>"><?=$worker['name'];?></a><?php if(isset($worker['categoryName'])) { ?><br /><small><?=$worker['categoryName'];?></small><?php } ?></td>
                                    <td><?php if($worker['persent']) echo "$worker[persent]%"; ?></td>
                                    <td>+<strong><?=round($worker['salary'], 2);?></strong> <?=$currency;?></td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                </div>
            </div>
        <?php } ?>
    
        <h2>Зарплата</h2>

        <table class="table">
            <tr>
                <th>Сотрудник</th>
                <th>Продолжительность работы</th>
                <th>Грязные</th>
                <th>Фикс</th>
                <th>Штрафы</th>
                <th>Бонусы</th>
                <th>Чистые</th>
                <th>К выплате</th>
                <th>Выплата</th>
            </tr>
            <?php $sumBonuses = 0; $sumFines = 0; $sumSalary = 0; $sumBalance = 0;?>
            <?php foreach($data['salary'] as $workerId => $workerData) { ?>
                <?php
                $sumSalary += $workerData['salary'];
                $sumBalance += $workerData['balance'];
                $sumBonuses += $workerData['bonuses'];
                $sumFines += $workerData['fines'];
                ?>
                <tr>
                    <td>
                        <p><a href="<?=Url::toRoute(['/staffer/staffer/view', 'id' => $workerId]);?>"><?=$workerData['staffer']->name;?></a></p>
                        <?php if($cat = $workerData['staffer']->category) { ?><p><small><?=$cat->name;?></small></p><?php } ?>
                    </td>
                    <td>
                        <?php
                        if($workerSessions = $workerData['staffer']->getSessionsBySession($session)) {
                            echo '<ul>';
                            foreach($workerSessions as $workSession) {
                                if($workSession->stop_timestamp) {
                                    $dateStop = date('H:i', $workSession->stop_timestamp);
                                } else {
                                    $dateStop = '...';
                                }
                                echo Html::tag(
                                    'li',
                                    Html::a(
                                        date('H:i', $workSession->start_timestamp).' - '.$dateStop,
                                        ['/order/order/index', 'time_start' => $workSession->start, 'time_stop' => $workSession->stop, 'element_types' => ['pistol88\service\models\Price', 'pistol88\service\models\CustomService']]
                                    )
                                );
                            }
                            echo '</ul>';
                        }
                        ?>
                    </td>
                    <td><?=$workerData['base_salary'];?></td>
                    <td><?php if($fix = $workerData['staffer']->fix) echo $fix; else echo '-';?></td>
                    <td><?=$workerData['fines'];?></td>
                    <td><?=$workerData['bonuses'];?></td>
                    <td><?=$workerData['salary'];?></td>
                    <td><?=$workerData['balance'];?></td>
                    <td>
                        <?php if($workerData['balance'] > 0) { ?>
                            <?= \pistol88\staffer\widgets\AddPayment::widget([
                                'staffer' => $workerData['staffer'],
                                'paymentSum' => round($workerData['balance'], 0, PHP_ROUND_HALF_DOWN),
                                'sessionId' => $session->id
                            ]); ?>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
            <tr>
                <td colspan="4" align="right">Итого:</td>
                <th><?=$sumFines;?></th>
                <th><?=$sumBonuses;?></th>
                <th><?=$sumSalary;?></th>
                <th><?=$sumBalance;?></th>
                <td></td>
            </tr>
        </table>
        
        <h2>Отчёт по кассам</h2>

        <?= \halumein\cashbox\widgets\ReportBalanceByPeriod::widget([
                'dateStart' => date('Y-m-d H:i:s', $session->start_timestamp),
                'dateStop' => $session->stop_timestamp ? date('Y-m-d H:i:s', $session->stop_timestamp) : null
                 ])
        ?>

        <?php if($paymentTypeReport = \pistol88\order\widgets\ReportPaymentTypes::widget([
                'types' => $module->paymentTypeIdsReport,
                'dateStart' => date('Y-m-d H:i:s', $session->start_timestamp),
                'dateStop' => $session->stop_timestamp ? date('Y-m-d H:i:s', $session->stop_timestamp) : null
             ])) { ?>
            <h2>Отчет по способам оплаты</h2>
            <?= $paymentTypeReport?> 
        <?php } ?>

        
        <h2>Отчёт по расходам</h2>

        <?= \halumein\spending\widgets\ReportSpendingsByPeriod::widget([
                'dateStart' => date('Y-m-d H:i:s', $session->start_timestamp),
                'dateStop' => $session->stop_timestamp ? date('Y-m-d H:i:s', $session->stop_timestamp) : null
                 ])
        ?>
    </div>
    <h2>Рабочий день</h2>

    <?=SessionGraph::widget(['workers' => $workers, 'control' => false, 'session' => $session, 'hoursCount' => $module->shiftDuration]);?>
    
<?php } ?>
</div>
