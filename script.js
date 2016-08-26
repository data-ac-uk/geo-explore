$(document).ready(
	function() {
		$('a.collapse-trigger').click(
			function() {
				var element = $(this);
				
				if (element.text() == 'show')
				{
					element.text('hide');
				}
				else 
				{
					element.text('show');
				}
			}
		);
	}
);