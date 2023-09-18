$(function () {
    "use strict";
   /* $.toast({
            heading: 'Bienvenidos al Sistema Ñamandú',
            text: 'Use the predefined ones, or specify a custom position object.',
            position: 'top-right',
            loaderBg: '#ff6849',
			bgColor : '#61a82b',
			textColor : '#ffffff',       
            icon: 'info',
            hideAfter: 5500,
            stack: 6
        })*/
 }); 
    var sparklineLogin = function () {
        $('#sparkline2dash').sparkline([20, 40, 30], {
            type: 'pie',
            height: '120',
            resize: true,
            sliceColors: ['#2b2b2b', '#36bea6', '#f6f6f6']
        });
        $('#sparkline1dash').sparkline([6, 10, 9, 11, 9, 10, 12], {
            type: 'bar',
            height: '150',
            barWidth: '4',
            resize: true,
            barSpacing: '10',
            barColor: '#25a6f7'
        });
        $('#sparkline3dash').sparkline([6, 10, 9, 11, 9, 10, 12], {
            type: 'bar',
            height: '150',
            barWidth: '4',
            resize: true,
            barSpacing: '10',
            barColor: '#f62d51'
        });
        $('#sparkline4dash').sparkline([6, 10, 9, 11, 9, 10, 12], {
            type: 'bar',
            height: '150',
            barWidth: '4',
            resize: true,
            barSpacing: '10',
            barColor: '#2b2b2b'
        });
    }
    var sparkResize;
    $(window).resize(function (e) {
        clearTimeout(sparkResize);
        sparkResize = setTimeout(sparklineLogin, 100);
    });
    sparklineLogin();

 
    // sparkline
    var sparklineLogin2 = function() {
	
        $('#grafico1').sparkline([1, 4, 5, 4, 5, 6, 6, 7, 5, 7, 5, 4, 3], {
        type: 'line',
        width: '100%',
        height: '75',
        lineColor: '#00bfbf',
        fillColor: '#ecfaf8',
        spotColor: undefined,
        minSpotColor: undefined,
        maxSpotColor: undefined,
        highlightSpotColor: undefined,
        highlightLineColor: '#494646',
        spotRadius: 0
        });
        
        $('#grafico2').sparkline([10, 12, 9, 11, 9, 10, 12], {
            type: 'bar',
            height: '130',
            barWidth: '4',
            width:'100%',
            resize: true,
            barSpacing: '8',
            barColor: '#25a6f7'
        });
		
		$('#grafico3').sparkline([8, 5, 1, 4, 5, 4, 6, 7, 5, 7, 5, 4, 10], {
        type: 'line',
        width: '100%',
        height: '75',
        lineColor: '#00bfbf',
        fillColor: '#ecfaf8',
        spotColor: undefined,
        minSpotColor: undefined,
        maxSpotColor: undefined,
        highlightSpotColor: undefined,
        highlightLineColor: '#494646',
        spotRadius: 0
        });
        
        $('#grafico4').sparkline([3, 5, 1, 3, 6, 6, 8], {
            type: 'bar',
            height: '130',
            barWidth: '4',
            width:'100%',
            resize: true,
            barSpacing: '8',
            barColor: '#25a6f7'
        });
        
    };    
    var sparkResize2;
 
	$(window).resize(function(e) {
		clearTimeout(sparkResize2);
		sparkResize2 = setTimeout(sparklineLogin2, 200);
	});
	sparklineLogin2();
	
	
