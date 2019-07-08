@extends('layouts.main')
@section('main_content')
<!-- MAIN CONTENT -->

	<div class="panel">	
        <div class="panel-body">
        	<div class="content-heading clearfix">
				<div class="heading-left">
					<h1 class="page-title">Tax Codes</h1>
					<hr>
					<!-- <p class="page-subtitle">Create new product</p> -->
				</div>
				<ul class="breadcrumb">
					
					<li><a href="#"><i class="fa fa-home"></i> Home</a></li>
					<li class="active"><a href="#">Tax Codes</a></li>
				</ul>

			</div>
			<div class="alert alert-success hide"></div>
        	<div class="form-horizontal">
        		<div class="pull-right">
					<a href="" class="btn btn-primary" id="btn-new-tax" data-toggle="modal" data-target="#modal-tax" data-modaltitle="Add Tax" data-action='add'><i class="fa fa-plus"></i> Add Tax Code</a>
					<br><br>
				</div>
				<div class="row">
					<div class="col-md-12">
						<table id="tax-table" class="table table-striped table-bordered">
						    <thead>
						        <tr>
						            <th width="30%">Tax Code Name</th>
						            <th width="30%">Tax Rate</th>
						            <th width="25%">Status</th>
						            <th width="15%">Action</th>
						        </tr>
						    </thead>

						    <tfoot>
						        <tr>
						            <th>Tax Code Name</th>
						            <th>Tax Rate</th>
						            <th>Status</th>
						            <th>Action</th>
						        </tr>
						    </tfoot>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal Tax -->
	<div class="modal fade" id="modal-tax" tabindex="-1" role="dialog" aria-labelledby="modal-tax" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" role="document">
	    	<div class="modal-content">
	    		<form id="form-tax">
		      		<div class="modal-header">
		        		<h4 class="modal-title" id="exampleModalCenterTitle"></h4>
		      		</div>		      		
		      		<div class="modal-body">						
			          	<div class="form-group" id="input-taxcode">
			            	<label for="taxcode" class="col-form-label">Tax Code Name:</label>
			            	<input type="text" class="form-control" name="taxcode" id="taxcode" placeholder="tax code name">
			          	</div>

			          	<div class="form-group" id="input-taxrate">
			            	<label for="taxrate" class="col-form-label">Tax Rate (%):</label>
			            	<input type="text" class="form-control" name="taxrate" id="taxrate" placeholder="tax rate">
			          	</div>
			          	<div class="form-group" id="input-status">
			          		<label for="taxrate" class="col-form-label">Status: </label>
							<div id="input-status">
								<label class="fancy-radio custom-bgcolor-green">
									<input name="status" id="status_active" value="Active" type="radio" checked="">
									<span><i></i>Active</span>
								</label>
								<label class="fancy-radio custom-bgcolor-green">
									<input name="status" id="status_inactive" value="Inactive" type="radio">
									<span><i></i>Inactive</span>
								</label>
							</div>
						</div>			          			        	
		      		</div>
			    	<div class="modal-footer">
				        <button type="button" id="btn-tax-close" class="btn btn-secondary" data-dismiss="modal">Close</button>
				        <button type="button" id="btn-save-tax" class="btn btn-primary">Save changes</button>
			    	</div>	
	      		</form>
	    	</div>
		</div>
	</div>
	<!-- END Modal Tax -->

	<!-- Modal Delete -->
	<div class="modal fade" id="modal-delete" tabindex="-1" role="dialog" aria-labelledby="modal-brand" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" role="document">
	    	<div class="modal-content">
	    		<form>
		      		<div class="modal-header">
		        		<h4 class="modal-title" id="exampleModalCenterTitle">Confirm Delete</h4>
		      		</div>		      		
		      		<div class="modal-body">	 				
						Are you sure you want to delete this taxcode?
		      		</div>
			    	<div class="modal-footer">
			    		 <a href="" id="btn-delete" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-trash"></i> Delete</a>
				        <button type="button" id="btn-delete-close" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
			    	</div>	
	      		</form>
	    	</div>
		</div>
	</div>
	<!-- END Modal Delete -->	

	<script>
	$(document).ready(function(){
	
		var url = '';
		var taxid = '';
		var tablerow = '';
		var taxtable =  $('#tax-table').DataTable();

		$('#btn-new-tax').click(function(e){
			e.preventDefault();
			var modaltitle = $('#btn-new-tax').data('modaltitle');
			
			$('.modal-title').empty();
			$('.modal-title').append(modaltitle);

			url = "{{route('storetax')}}";
			
		});

		$('#tax-table').on('click', 'tbody td #btn-edit-tax' ,function(e){
			
	        e.preventDefault();

			var modaltitle = $(this).data('modaltitle');
			
			$('.modal-title').empty();
			$('.modal-title').append(modaltitle);

			url = "{{route('updatetax')}}";
			taxid = $(this).data('taxid');

			$.ajax({
				url: "{{route('edittax')}}",
				method: "GET",
				data: { taxid:taxid },
				success: function(response){
					console.log(response);
					
					$('#taxcode').val(response.taxes.taxcode);
					$('#taxrate').val(response.taxes.taxrate);

					if(response.taxes.status == 'Active')
					{	
						$("#status_active").prop("checked", true);
					}
					else
					{	
						$("#status_inactive").prop("checked", true);
					}

				},
				error: function(response){
					console.log(response);
				}
			});


   		});

		$('#btn-save-tax').click(function(e){
			e.preventDefault();

			var taxcode = $('#taxcode').val();
			var taxrate = $('#taxrate').val();
			var status = '';
			
			if($('#status_active').is(':checked') == true)
			{
				status = 'Active';
			}
			else
			{
				status = 'Inactive';
			}
	
			$.ajax({
				url: url,
				method: "POST",
				data: {
					_token: "{{csrf_token()}}",
					taxid: taxid,
					taxcode: taxcode,
					taxrate: taxrate,
					status: status
				},
				success: function(response){
					console.log(response);
					
					var taxes = Object.keys(response.taxes);
					var rows = Object.keys(response.taxes).length;

					if(response.success_store)
					{	

						$('.alert').empty();
						$('.alert').show();
						$('.alert').append("<i class='fa fa-check'></i> "+response.success_store);
						$('.alert').removeClass('hide');
						$('.alert').delay(5000).fadeOut();
						$('#modal-tax').modal('hide'); 
						$('#form-tax')[0].reset();
						taxtable.ajax.reload();
						
					}
					else if(response.success_update)
					{	
						
						$('.alert').empty();
						$('.alert').show();
						$('.alert').append("<i class='fa fa-check'></i> "+response.success_update);
						$('.alert').removeClass('hide');
						$('.alert').delay(5000).fadeOut();
						$('#modal-tax').modal('hide'); 
						$('#form-tax')[0].reset();
						taxtable.ajax.reload();    
						
					}
					else
					{
						//GET Object Names and Object Length
						var result = Object.keys(response);
						var rows = Object.keys(response).length;
						
						//START for loop display errors
						for(x=0; rows > x; x++ )
						{
							
							if(response[result[x]].length == 1)
							{	

								$('#input-'+result[x]).addClass('has-error');
								$('#input-'+result[x]).append("<span class='text-danger' id='error-"+result[x]+"'><i>"+response[result[x]]+"</i></span>");
							}

							
						}


			        	$('#taxcode').keyup(function(e){
			        		e.preventDefault();

			        		if($('#taxcode').val() == '')
			        		{	
			        			$('#input-taxcode').addClass('has-error');
			        			$('#error-taxcode').remove();
			        			$('#input-taxcode').append("<span class='text-danger' id='error-taxcode'><i>"+response['taxcode']+"</i><span");
			        		}
			        		else
			        		{
			        			$('#error-taxcode').remove();
				        		$('#input-taxcode').removeClass('has-error');
			        		}
			        	});

			        	$('#taxrate').keyup(function(e){
			        		e.preventDefault();

			        		if($('#taxrate').val() == '')
			        		{	
			        			$('#input-taxrate').addClass('has-error');
			        			$('#error-taxrate').remove();
			        			$('#input-taxrate').append("<span class='text-danger' id='error-taxrate'><i>"+response['taxrate']+"</i><span");
			        		}
			        		else
			        		{
			        			$('#error-taxrate').remove();
				        		$('#input-taxrate').removeClass('has-error');
			        		}
			        	});
					}
				},
				error: function(response){
					console.log(response);
				}
			});

		});

		$('#tax-table').on('click', 'tbody td #btn-delete-tax' ,function(e){
			
	        e.preventDefault();

			taxid = $(this).data('taxid');

			$('#btn-delete').click(function(e){
				e.preventDefault();
				$.ajax({
					url: "{{route('deletetax')}}",
					method: "GET",
					data: { taxid:taxid },
					success: function(response){

						console.log(response);
						taxtable.ajax.reload();
						$('.alert').empty();
						$('.alert').append("<i class='fa fa-check'></i> "+response.success);
						$('.alert').removeClass('hide');
						$('.alert').delay(5000).fadeOut();
						$('#modal-tax').modal('hide')
					},
					error: function(response){
						console.log(response);
					}
				});
			});

   		});

		$('#btn-tax-close').click(function(e){
			e.preventDefault();
			$('#taxcode').val('');
			$('#taxrate').val('');
		});

	});
</script>

@endsection

@section('datatable')
<script>

	$(document).ready(function() {
		// $('#tax-table').DataTable();
	    $('#tax-table').DataTable({
	    	"processing": true,
	    	"serverSide": true,
	    	"ajax": "{{route('gettax')}}",
	    	"columns": [
	    		{ "data": "taxcode"},
	    		{ "data": "taxrate"},
	    		{ "data": "status"},
	    		{ "data": "action", orderable: false, searchable: false}
	    	]
	    });

		
	});
</script>
@endsection

