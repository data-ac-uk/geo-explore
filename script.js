$(document).ready(
	function() {
		$('a.collapse-trigger').click(
			function() {
				var element = $(this);
				var glyph = element.find( '.glyphicon' );
				if( glyph.hasClass( 'glyphicon-plus' ))
				{
					glyph.removeClass( 'glyphicon-plus' );
					glyph.addClass( 'glyphicon-minus' );
				}
				else 
				{
					glyph.addClass( 'glyphicon-plus' );
					glyph.removeClass( 'glyphicon-minus' );
				}
			}
		);
	}
);
