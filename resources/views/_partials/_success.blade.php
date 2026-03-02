@if(Session::has('success'))
	<div class="alert alert-success flush">
		<i class="fa fa-check"> </i> {{Session::get('success')}}
	</div>
@endif

<!-- jQuery -->
<script src="{{url('assets/js/core/jquery.min.js')}}"></script>
<script type="text/javascript">
	$('.flush').delay(4000).fadeOut();
</script>
