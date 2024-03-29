<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Ficha del postulante</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <style type="text/css">
        @page {
            margin: 200px 50px 120px 50px;
        }

        #header {
            position: fixed;
            left: 0px;
            top: -130px;
            right: 0px;
            height: 100px;
            background-color: white;
            text-align: center;
            border-bottom: 1px solid gray;
        }

        #footer {
            position: fixed;
            left: 0px;
            bottom: -180px;
            right: 40px;
            height: 150px;
            background-color: white;
            text-align: center;
            font-size: 10px;
        }


        html {
            font-size: 87.5%;
        }

        body {
            font-family: Arial, sans-serif;
            margin-left: 40px;
            margin-right: 40px;
        }

        h3 {
            text-align: center;
        }

        .td-right {
            text-align: right;
        }
    </style>

</head>

<body>
    <script type="text/php">
			if ( isset($pdf) ) {
				$size = 8;
				$color = array(0,0,0);
				$font = $fontMetrics->getFont("Arial");
				$text_height = $fontMetrics->getFontHeight($font, $size);

				$foot = $pdf->open_object();

				$w = $pdf->get_width();
				$h = $pdf->get_height();

				// Fecha
				$date = date("d").str_pad(' ',20);
				switch(date("m")) {
					case "01": echo $date.= "enero"; break;
					case "02": echo $date.= "febrero"; break;
					case "03": echo $date.= "marzo"; break;
					case "04": echo $date.= "abril"; break;
					case "05": echo $date.= "mayo"; break;
					case "06": echo $date.= "junio"; break;
					case "07": echo $date.= "julio"; break;
					case "08": echo $date.= "agosto"; break;
					case "09": echo $date.= "setiembre"; break;
					case "10": echo $date.= "octubre"; break;
					case "11": echo $date.= "noviembre"; break;
					case "12": echo $date.= "diciembre"; break;
			   }
				$date.= str_pad(' ',20).date("Y");
				$d_width = $fontMetrics->getTextWidth($date, $font, $size);
				$pdf->page_text($w - $d_width - 50, $h - $text_height - 130, $date, $font, $size, $color);

				// Paginación
				$page = "{PAGE_NUM}/{PAGE_COUNT}";
				$p_width = $fontMetrics->getTextWidth("0/0", $font, $size);
				$pdf->page_text($w - $p_width - 50, $h - $text_height - 40, $page, $font, $size, $color);

				$pdf->close_object();
				$pdf->add_object($foot, "all");
			}
		</script>
    <div id="header">
        @yield('header')
    </div>
    <div id="footer">
        @yield('footer')
    </div>
    <div id="content">
        @yield('content')
        <p style="page-break-before:auto;"></p>
    </div>
</body>

</html>
