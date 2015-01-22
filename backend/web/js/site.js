$(function() {
	$('[data-toggle="tooltip"]').tooltip();
	$(document).on('pjax:complete', function() {
		$('[data-toggle="tooltip"]').tooltip();
	})
	$(document).on('change', 'input[type=checkbox].resolve', function() {
		var $this = $(this);
		var params = {
			version: 1,
			hash: $this.data('hash'),
			attribute: $this.data('attribute')
		};
		if (!$this.is(':checked')) {
			params.version = 0;
		}
		$this.hide();
		$.post('/resolve', params, function() {
			$this.parents('tr').toggleClass('alert-success', params.version > 0);
		}).complete(function() {
			$this.show();
		});
	});
});