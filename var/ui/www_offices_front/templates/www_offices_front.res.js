$(document).ready(function(){
	var coord  = $('.city_coord').attr('data-coord');
	var area = $('.map');

	var styles = [ { "stylers": [ { "saturation": -87 } ] } ];
	var styledMap = new google.maps.StyledMapType(styles,
	    {name: "Styled Map"});

	if(coord){
			var arr = coord.split(',');
			var myLatlng = new google.maps.LatLng(arr[0],arr[1]);
			var mapOptions = {
				zoom: 11,
				center: myLatlng,
				mapTypeControlOptions: {
				      mapTypeIds: [google.maps.MapTypeId.ROADMAP, 'map_style']
				}

			}
			var map = new google.maps.Map(area[0], mapOptions);
			map.mapTypes.set('map_style', styledMap);
			map.setMapTypeId('map_style');

	}
	$('.office_coord').each(function(el){
		var coord  = $(this).attr('data-coord');
		if(coord){
		var image = {
			url: '/themes/priv/img/map_ico.png',
			size: new google.maps.Size(39, 55),
			origin: new google.maps.Point(0,0),
			anchor: new google.maps.Point(0, 55)
			};
			var shape = {
				coord: [1, 1, 1, 39, 55, 39, 55 , 1],
				type: 'poly'
			};
			var contentString = $(this).html();
			var infowindow = new google.maps.InfoWindow({
				content: contentString
			});
			var arr = coord.split(',');
			var myLatlng = new google.maps.LatLng(arr[0],arr[1]);
			var marker = new google.maps.Marker({
				position: myLatlng,
				map: map,
				icon: image,
				shape: shape,
				title: $(this).attr('data-title')
			});
			google.maps.event.addListener(marker, 'click', function() {
				    infowindow.open(map,marker);
			});
		}
	})
	$('.office_selector').on('change',function(){
		var sel = $(this).val();
		window.open("/catalog/?cty="+sel,"_self");
	});
});
