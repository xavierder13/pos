@extends('layouts.main')

@section('main_content')
<!-- MAIN CONTENT -->

	<div class="panel">	
        <div class="panel-body">
        	<div class="content-heading clearfix">
				<div class="heading-left">
					<h1 class="page-title">Product</h1>
					<hr>
					<p class="page-subtitle">Create new product</p>
				</div>
				<ul class="breadcrumb">
					<li><a href="#"><i class="fa fa-home"></i> Home</a></li>
					<li><a href="{{route('viewproduct')}}">Products</a></li>
					<li class="active">Add Product</li>
				</ul>
			</div>

			<div class="alert alert-success hide"></div>

        	<form id="form-product">
        		<div class="form-horizontal">
					<div class="row">
						<div class="col-md-12">
							<div class="form-group" id="form-itemcode">
								<label class="col-md-2 control-label">Item Code <span class="text-danger">*</span></label>
								<div class="col-md-6" id="input-itemcode">
									<input type="text" name="itemcode" id="itemcode" class="form-control" placeholder="item code">	
								</div>
							</div>

							<div class="form-group" id="form-itemname">
								<label class="col-md-2 control-label">Item Name <span class="text-danger">*</span></label>
								<div class="col-md-6" id="input-itemname">
									<input type="text" name="itemname" id="itemname" class="form-control" placeholder="item name">
								</div>
							</div>	

							<div class="form-group" id="form-dp">
								<label class="col-md-2 control-label">DP <span class="text-danger">*</span></label>
								<div class="col-md-6" id="input-dp">
									<input type="text" name="dp" id="dp" class="form-control" placeholder="dp">
								</div>
							</div>
							<div class="form-group" id="form-srp">
								<label class="col-md-2 control-label">SRP <span class="text-danger">*</span></label>
								<div class="col-md-2" id="input-srp">
									<input type="text" name="srp" id="srp" class="form-control" placeholder="srp">
									<span id="span-srp"><i>tax-exclusive price</i></span>
								</div>
								<div class="col-md-1 text-center"> + </div>
								<div class="col-md-2" id="input-tax">
									<select class="form-control" name="tax" id="tax">
										<option value="default" selected="" data-tax="12">Default: VAT 12.00%</option>
										@foreach($taxes as $tax)
										<option value="{{$tax->id}}" data-tax="{{$tax->taxrate}}">{{$tax->taxcode}} {{$tax->taxrate}}{{'%'}}</option>
										@endforeach		
									</select>
									<span><i>tax</i></span>
								</div>
								<div class="col-md-1 text-center"> = </div>
								<div class="col-md-2" id="input-srp-tax">
									<input type="text" name="srp-tax" id="srp-tax" class="form-control" placeholder="" disabled="">
									<span><i>tax-inclusive price</i></span>
								</div>

							</div>			
							<div class="form-group" id="form-category">
								<label class="col-md-2 control-label">Category</label>
								<div class="col-md-6" id="input-category">
									<div class="col-md-12 input-group">
										<select class="form-control" name="category" id="category">
											<option selected="" disabled="" > (No Category) </option>
											@foreach($categories as $category)
											<option value="{{$category->id}}"> {{$category->category}} </option>
											@endforeach
										</select>
										<a class="input-group-addon" href="" id="add-category" data-toggle="modal" data-target="#modal-category">	
											<i class="fa fa-plus"></i>									
										</a>			
									</div>
								</div>
							</div>
							<div class="form-group" id="form-brand">
								<label class="col-md-2 control-label">Brand </label>
								<div class="col-md-6" id="input-brand">
									<div class="col-md-12 input-group">
										<select class="form-control" name="brand" id="brand">
											<option selected="" disabled="" > (No Brand) </option>
											@foreach($brands as $brand)
											<option value="{{$brand->id}}"> {{$brand->brand}} </option>
											@endforeach
										</select>
										<a class="input-group-addon" href="" id="add-brand" data-toggle="modal" data-target="#modal-brand">	
											<i class="fa fa-plus"></i>									
										</a>			
									</div>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-2 control-label">Status</label>
								<div class="col-md-2" id="input-status">
									<label class="fancy-radio custom-bgcolor-green">
										<input name="status" id="status" value="Active" type="radio" checked>
										<span><i></i>Active</span>
									</label>
									<label class="fancy-radio custom-bgcolor-green">
										<input name="status" id="status" value="Inactive" type="radio">
										<span><i></i>Inactive</span>
									</label>
								</div>
							</div>	
						</div>
					</div>
					<div class="panel-footer">		
				        <button type="submit" id="btn-add-product" class="btn btn-primary">Save</button>
				        <!-- <button type="submit" id="btn-add-create-product" class="btn btn-primary">Save and Create New</button>	 -->
				        <button type="reset" class="btn btn-secondary">Cancel</button>	
					</div>
				</div>	
        	</form>		
		</div>								
    </div>

	<!-- Modal Category -->
	<div class="modal fade" id="modal-category" tabindex="-1" role="dialog" aria-labelledby="modal-category" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" role="document">
	    	<div class="modal-content">
	    		<form>
		      		<div class="modal-header">
		        		<h4 class="modal-title" id="exampleModalCenterTitle">Add Category</h4>
		      		</div>
		      		<div class="modal-body">
		      			<div class="form-group" id="input-new-category">
			            	<label for="category" class="col-form-label">Category:</label>	
							<input type="text" name="new-category" id="new-category" class="form-control" placeholder="category">
						</div>
		      		</div>
			    	<div class="modal-footer">
				        <button type="button" id="btn-category-close" class="btn btn-secondary" data-dismiss="modal">Close</button>
				        <button type="button" id="btn-add-category" class="btn btn-primary">Save changes</button>
			    	</div>	
	      		</form>
	    	</div>
		</div>
	</div>
	<!-- END Modal Category -->

	<!-- Modal Category -->
	<div class="modal fade" id="modal-brand" tabindex="-1" role="dialog" aria-labelledby="modal-brand" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" role="document">
	    	<div class="modal-content">
	    		<form>
		      		<div class="modal-header">
		        		<h4 class="modal-title" id="exampleModalCenterTitle">Add Brand</h4>
		      		</div>		      		
		      		<div class="modal-body">	
		      			<div class="form-group" id="input-new-brand">
			            	<label for="brand" class="col-form-label">Brand:</label>	 				
							<input type="text" name="new-brand" id="new-brand" class="form-control" placeholder="brand">
						</div>
		      		</div>
			    	<div class="modal-footer">
				        <button type="button" id="btn-brand-close" class="btn btn-secondary" data-dismiss="modal">Close</button>
				        <button type="button" id="btn-add-brand" class="btn btn-primary">Save changes</button>
			    	</div>	
	      		</form>
	    	</div>
		</div>
	</div>
	<!-- END Modal Category -->	
<!-- END MAIN CONTENT -->

<script>
	

	$(document).ready(function(){
		     
		//START Enter SRP
		$('#srp').keyup(function(e){
			e.preventDefault();
			var taxrate = $('#tax').find(':selected').data('tax') / 100;
			var srp = $('#srp').val();
			var srp_tax = parseInt(srp) + (srp * taxrate)

			if(srp != '')
			{
				if($.isNumeric(srp))
				{
					$('#srp-tax').val(srp_tax);
				}
				else
				{
					$('#srp-tax').val('');
				}
			}
			
		});
		//END Enyer SRP

		//START Add Product
		$('#btn-add-product ').click(function(e){
			e.preventDefault();
			var itemcode = $('#itemcode').val();
			var itemname = $('#itemname').val();
			var dp = $('#dp').val();
			var srp = $('#srp').val();
			var category = $('#category').val();
			var brand = $('#brand').val();
			var tax = $('#tax').val();
			var status = $('#status').val();
			
			$.ajax({
				url: "{{route('storeproduct')}}",
				method: "POST",
				data: {
					_token: "{{csrf_token()}}",
					itemcode: itemcode,
					itemname: itemname,
					dp: dp,
					srp: srp,
					category: category,
					brand: brand,
					tax: tax,
					status: status
				},

				success: function(response){
					$('#form-itemcode').removeClass('has-error');
					$('#error-itemcode').remove();
					$('#form-itemname').removeClass('has-error');
					$('#error-itemname').remove();
					$('#form-dp').removeClass('has-error');
					$('#error-dp').remove();
					$('#form-srp').removeClass('has-error');
					$('#error-srp').remove();
					
					if (response.success)
					{	
						$('.alert').empty();
						$('.alert').show();
						$('.alert').append("<i class='fa fa-check'></i> "+response.success);
						$('.alert').removeClass('hide');
						$('.alert').delay(5000).fadeOut(); 
						$('#form-product')[0].reset();
						$(window).scrollTop(0);
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

								$('#form-'+result[x]).addClass('has-error');
								$('#input-'+result[x]).append("<span class='text-danger' id='error-"+result[x]+"'><i>"+response[result[x]]+"</i></span>");
							}
						
						}

						$('#itemcode').keyup(function(e){
							e.preventDefault();
							if($('#itemcode').val() == '')
							{
								$('#form-itemcode').addClass('has-error');
								$('#error-itemcode').remove();
								$('#input-itemcode').append("<span class='text-danger' id='error-itemcode'><i>This field is required</i></span>");
							}
							else
							{
								$('#form-itemcode').removeClass('has-error');
								$('#error-itemcode').remove();
							}
							
						});

						$('#itemname').keyup(function(e){
							e.preventDefault();
							if($('#itemname').val() == '')
							{
								$('#form-itemname').addClass('has-error');
								$('#error-itemname').remove();
								$('#input-itemname').append("<span class='text-danger' id='error-itemname'><i>This field is required</i></span>");
							}
							else
							{
								$('#form-itemname').removeClass('has-error');
								$('#error-itemname').remove();
							}
							
						});

						$('#dp').keyup(function(e){
							
							e.preventDefault();
							if($('#dp').val() == '')
							{
								$('#form-dp').addClass('has-error');
								$('#error-dp').remove();
								$('#input-dp').append("<span class='text-danger' id='error-dp'><i>This field is required</i></span>");
							}
							else if($.isNumeric($('#dp').val()) == false)
							{
								$('#form-dp').addClass('has-error');
								$('#error-dp').remove();
								$('#input-dp').append("<span class='text-danger' id='error-dp'><i>Please enter a valid number</i></span>");
							}
							else
							{
								$('#form-dp').removeClass('has-error');
								$('#error-dp').remove();
							}
							
						});

						$('#srp').keyup(function(e){
							
							e.preventDefault();
							if($('#srp').val() == '')
							{
								$('#form-srp').addClass('has-error');
								$('#error-srp').remove();
								$('#input-srp').append("<span class='text-danger' id='error-srp'><i>This field is required</i></span>");
								$('#srp-tax').val('');
								$('#span-srp').hide();
							}
							else if($.isNumeric($('#srp').val()) == false)
							{
								$('#form-srp').addClass('has-error');
								$('#error-srp').remove();
								$('#input-srp').append("<span class='text-danger' id='error-srp'><i>Please enter a valid number</i></span>");
								$('#srp-tax').val('');
							}
							else
							{
								$('#form-srp').removeClass('has-error');
								$('#error-srp').remove();
								$('#span-srp').show();
							}
							
						});

						$('#span-srp').hide();
						//END for loop display errors
			
					}	

					console.log(response);		
				},
				error: function(response){
					console.log(response);
				}
			});
		});
		//END Add Product
		
		// //START Modal Add Category	

		$('#btn-add-category').click(function(e){

			e.preventDefault();
			$.ajax({

				url: "{{route('storecategory')}}",
				method: "POST",
				data: {
					_token: "{{csrf_token()}}",
					category: $('#new-category').val()
				},
				success: function(response){
					console.log(response);

			        if(response.success)
			        {	
			        	$('#category').find('option').remove(); //remove all select options 
			        	$('#category').append('<option value="">(No Category)</option>');
			        	var rows = Object.keys(response.categories).length;
			        	for(x=0; x<rows; x++)
			        	{	
			        		var selected = '';

			        		if((rows-1) == x)
			        		{
			        			selected = 'selected';
			        		}

			        		$('#category').append("<option value='"+response.categories[x].id+"' "+selected+">"+response.categories[x].category+"</option>")
			        	}

			        	$('#modal-category').modal('hide');
				        $('#new-option-category').attr('selected', true);
				        $("#new-category").val('');	
				        $('#error-new-category').remove();
				        $('#input-new-category').removeClass('has-error');
			        }
			        else
			        {	
			        	$('#input-new-category').addClass('has-error');
			        	$('#error-new-category').remove();
			        	$('#input-new-category').append("<span class='text-danger' id='error-new-category'><i>"+response['category']+"</i><span");


			        	$('#new-category').keyup(function(e){
			        		e.preventDefault();

			        		if($('#new-category').val() == '')
			        		{	
			        			$('#input-new-category').addClass('has-error');
			        			$('#error-new-category').remove();
			        			$('#input-new-category').append("<span class='text-danger' id='error-new-category'><i>"+response['category']+"</i><span");
			        		}
			        		else
			        		{
			        			$('#error-new-category').remove();
				        		$('#input-new-category').removeClass('has-error');
			        		}
			        	});
				        
			        }
			                    
	           },
	           error: function(response){
	           		console.log(response);
	           }
			});

		});

		$('#btn-category-close').click(function(e){
			e.preventDefault();
			$('#new-category').val('');
			$('#error-new-category').remove();
			$('#input-new-category').removeClass('has-error');
		});
		//END Modal Add Category

		//START Modal Add Brand

		$('#btn-add-brand').click(function(e){
			e.preventDefault();

			$.ajax({
				url: "{{route('storebrand')}}",
				method: "POST",
				data: {
					_token: "{{csrf_token()}}",
					brand: $('#new-brand').val()
				},
				success: function(response){
					console.log(response);

			        if(response.success)
			        {	
			        	$('#brand').find('option').remove(); //remove all select options 
			        	$('#brand').append('<option value="">(No Category)</option>');
			        	var rows = Object.keys(response.brands).length;
			        	for(x=0; x<rows; x++)
			        	{	
			        		var selected = '';

			        		if((rows-1) == x)
			        		{
			        			selected = 'selected';
			        		}

			        		$('#brand').append("<option value='"+response.brands[x].id+"' "+selected+">"+response.brands[x].brand+"</option>")
			        	}

			        	$('#modal-brand').modal('hide');
				        $('#new-option-brand').attr('selected', true);
				        $("#new-brand").val('');	
				        $('#error-new-brand').remove();
				        $('#input-new-brand').removeClass('has-error');
			        }
			        else
			        {	
			        	$('#input-new-brand').addClass('has-error');
			        	$('#error-new-brand').remove();
			        	$('#input-new-brand').append("<span class='text-danger' id='error-new-brand'><i>"+response['brand']+"</i><span");


			        	$('#new-brand').keyup(function(e){
			        		e.preventDefault();

			        		if($('#new-brand').val() == '')
			        		{	
			        			$('#input-new-brand').addClass('has-error');
			        			$('#error-new-brand').remove();
			        			$('#input-new-brand').append("<span class='text-danger' id='error-new-brand'><i>"+response['brand']+"</i><span");
			        		}
			        		else
			        		{
			        			$('#error-new-brand').remove();
				        		$('#input-new-brand').removeClass('has-error');
			        		}
			        	});
				        
			        }
				},
	           	error: function(response){
	           		console.log(response);
	           	}
			});
		});

		$('#btn-brand-close').click(function(e){
			e.preventDefault();
			$('#new-brand').val('');
			$('#error-new-brand').remove();
			$('#input-new-brand').removeClass('has-error');
		});
		//END Modal Add Brand

	});
	

</script>

@endsection