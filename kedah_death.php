<?php
/*/***
 * date: yyyy-mm-dd format; data correct as of 1200hrs on that date
 * state: name of state (present in state file, but not country file)
 * deaths
 * bid
 */

// get data from the server or local path
$server_path = "https://raw.githubusercontent.com/MoH-Malaysia/covid19-public/main/epidemic/deaths_state.csv";
$local_path = "cases.csv";

// temp will be using local path
$path = $server_path;

$row = 0;
$data = [];
$findMe = "Kedah";

$str  = "";
if (($handle = fopen($path, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000)) !== FALSE) {
        $pos = strpos($data[1], $findMe);
        if ($pos !== false) {
            $str.="{date:\"".$data[0]."\",death:".intval($data[2]).",bid:".intval($data[3])."},\n";

        }

        $row++;
    }
    fclose($handle);
}
?>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Analisa data covid-19 di Kedah  </title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/css/bootstrap.min.css" rel="stylesheet"
          id="bootstrap-css">
    <link href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
    <!------ Include the above in your HEAD tag ---------->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.8.4/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.5.0/chart.min.js" integrity="sha512-asxKqQghC1oBShyhiBwA+YgotaSYKxGP1rcSYTDrB0U6DxwlJjU59B67U8+5/++uFjcuVM8Hh5cokLjZlhm3Vg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script async
            src="https://www.googletagmanager.com/gtag/js?id=UA-129654074-1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        gtag('js', new Date());

        gtag('config', 'UA-129654074-1');


    </script>
</head>
<body>
<div class="container-fluid" style="background: #D3D3D3">

    <canvas id="myCanvas" height="250" width="250"></canvas>

    <script>
        const data = [
            <?php echo $str; ?>
        ];

        new Chart("myCanvas", {
            type: 'line',
            data: {
                labels: data.map(o => o.date ),
                datasets: [{
                    label: "Kematian",
                    fill: false,
                    borderColor: "rgba(59, 89, 152, 1)",
                    data: data.map(o => o.death)
                },{
                    label: "Kematian Dari Rumah",
                    fill: false,
                    borderColor: "rgba(255, 0, 0, 1)",
                    data: data.map(o => o.bid)
                }],
            },
            options: {
                maintainAspectRatio: false,
                responsive:true,
                scales: {
                    xAxes: [{
                        type: 'date',
                        time: {
                            unit: 'day'
                        }
                    }]
                }
            }
        });
    </script>
</div>
</body>
</html>