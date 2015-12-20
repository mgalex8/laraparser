$(document).ready(function(){	
	
	$('#parse_btn').click(function()
	{		
		var maximum = 50;		
		var parseCount = 5;
		var parseGroupIteration = Math.trunc(maximum / parseCount);
		
		$('.loading').data('count', 0);
		$('.loading-bar').css('width', 0);				
		
		var maxWidth = 744;		
		
		for(i=0; i<parseGroupIteration; i++) {
			$.ajax({
				url: '/parser/',
				method:'get',
				data:{
					count: parseCount,
				},
				asynh: false,
				dataType: 'json',
				success: function(response) {
					console.log(response);
					if (response.success == 1) 
					{
						$('.loading-url').html('');						
						$.each(response.links, function( key, value ) {							
							$('.loading-url').append('<div>'+value+'</div>');
						});
						
						var count = $('.loading').data('count');
						count += response.links.length;
						if (count >= maximum) {
							var barWidth = maxWidth;							
							$('.loading').data('count', maximum);
							$('.loading-bar').css('width', barWidth);
						}
						else {
							var barWidth = Math.trunc(maxWidth / maximum * count);							
							$('.loading').data('count', count);
							$('.loading-bar').css('width', barWidth);
						}
					}
				},			
				error: function() {
					console.log('error');
				}
			})
		}
	});
	
});
