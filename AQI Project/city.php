<?php
require __DIR__ . '/inc/functions.inc.php';

$city = null;

if(!empty($_GET['city'])) {
    $city = $_GET['city'];
}
$filename = null;
$cityInformation = [];
if(!empty($city)) {

    $cities = json_decode(file_get_contents(__DIR__ . '/data/index.json'), true);

    foreach($cities AS $c) {
        if($c['city'] === $city) {
            $filename = $c['filename'];
            $cityInformation = $c;
            break;
        }
    }
}
$stats = [];
if(!empty($filename)) {
    $results = json_decode(file_get_contents('compress.bzip2://' . __DIR__ . '/data/' . $filename), true)['results'];

    $units = [
        'pm25' => null,
        'pm10' => null
    ];

    foreach ($results as $result) {
        if(!empty($units['pm25'] && !empty($units['pm10']))) break;
        if($result['parameter'] === 'pm25') {
            $units['pm25'] = $result['unit'];
        }
        if($result['parameter'] === 'pm10') {
            $units['pm10'] = $result['unit'];
        }
    }
    
    foreach($results AS $result) {
        if(($result['parameter'] !== 'pm25' && $result['parameter'] !== 'pm10') || $result['value'] < 0) continue;
        // var_dump($result);
        $month = substr($result['date']['local'], 0, 7);

        if(!isset($stats[$month])) {
            $stats[$month] = [
                'pm25' => [],
                'pm10' => []
            ];
        }
        $stats[$month][$result['parameter']][] = $result['value'];
    }
    // var_dump($stats);
}
?>

<?php require __DIR__ . '/views/header.inc.php'; ?>

<?php if(empty($city)) : ?>
    <p>City Could not be loaded!</p>
<?php else: ?> 
    <h1><?php echo e($cityInformation['city']); ?></h1>
    <?php
    if(!empty($stats)): ?>
    <canvas id="aqi-chart" style="width: 300px; height: 300px;"></canvas>
    <script src="./scripts/chart.umd.js"></script>
    <?php 
        $labels = array_keys($stats);
        sort($labels);
        $pm25 = [];
        $pm10 = [];
        foreach ($labels as $label) {
            $measurements = $stats[$label];
            $pm25[] = round(array_sum($measurements['pm25'])/count($measurements['pm25']), 5);
            $pm10[] = round(array_sum($measurements['pm10'])/count($measurements['pm10']), 5);
        }
    ?>
    <script>
        const ctx = document.getElementById('aqi-chart');
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [
                {
                    label: <?php echo json_encode("AQI, PM2.5 in {$units['pm25']}"); ?>,
                    data: <?php echo json_encode($pm25); ?>,
                    fill: false,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                },
                {
                    label: <?php echo json_encode("AQI, PM10 in {$units['pm10']}"); ?>,
                    data: <?php echo json_encode($pm10); ?>,
                    fill: false,
                    borderColor: 'rgb(192, 75, 192)',
                    tension: 0.1
                }
            ]
            },
            options: {
                onClick: (e) => {
                const canvasPosition = getRelativePosition(e, chart);

                // Substitute the appropriate scale IDs
                const dataX = chart.scales.x.getValueForPixel(canvasPosition.x);
                const dataY = chart.scales.y.getValueForPixel(canvasPosition.y);
                }
            }
        });
    </script>
    <table>
        <thead>
            <tr>
                <td>Month</td>
                <td>PM 2.5 Concentration</td>
                <td>PM 1.0 Concentration</td>
            </tr>
        </thead>
        <tbody>
            <?php foreach($stats AS $month => $measurements): ?>
                <tr>
                    <th><?php echo e($month); ?></th>
                    <td><?php echo e(round(array_sum($measurements['pm25'])/count($measurements['pm25']), 2)); echo e($units['pm25']); ?> </td>
                    <td><?php echo e(round(array_sum($measurements['pm10'])/count($measurements['pm10']), 2)); echo e($units['pm25']);?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        
    </table>
    <?php endif; 
endif; ?>

<?php require __DIR__ . '/views/footer.inc.php'; ?>