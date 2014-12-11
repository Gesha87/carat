$(function() {
	$('[data-toggle="tooltip"]').tooltip();
	$('input[type=checkbox].resolve').change(function() {
		var $this = $(this);
		var params = {
			version: $this.data('version'),
			hash: $this.data('hash'),
			attribute: $this.data('attribute')
		};
		if (!$this.is(':checked')) {
			params.version = 0;
		}
		$this.hide();
		$.post('/resolve', params, function() {
			$this.parents('tr').toggleClass('alert-success', params.version > 0);
		}, 'json').complete(function() {
			$this.show();
		});
	});
});