@extends('layouts.main')

@section('main_content')
<!-- MAIN CONTENT -->

	<div class="panel">	
        <div class="panel-body">
        	<div class="content-heading clearfix">
				<div class="heading-left">
					<h1 class="page-title">Customer</h1>
					<hr>
					<p class="page-subtitle">Edit customer</p>
				</div>
				<ul class="breadcrumb">
					<li><a href="#"><i class="fa fa-home"></i> Home</a></li>
					<li><a href="{{route('viewcustomer')}}">Customer</a></li>
					<li class="active">Edit Customer</li>
				</ul>
			</div>

			<div class="alert alert-success hide"></div>
			
        	<form id="form-customer">
        		<div class="form-horizontal">
					<div class="row">
						<div class="col-md-12">
							<div class="form-group" id="form-firstname">
								<label class="col-md-2 control-label">First Name <span class="text-danger">*</span></label>
								<div class="col-md-6" id="input-firstname">
									<input type="text" name="firstname" id="firstname" class="form-control" placeholder="first name" value="{{$customers->firstname}}">	
								</div>
							</div>
							<div class="form-group" id="form-lastname">
								<label class="col-md-2 control-label">Last Name <span class="text-danger">*</span></label>
								<div class="col-md-6" id="input-lastname">
									<input type="text" name="lastname" id="lastname" class="form-control" placeholder="last name"  value="{{$customers->lastname}}">	
								</div>
							</div>
							<div class="form-group" id="form-phone">
								<label class="col-md-2 control-label">Phone</label>
								<div class="col-md-6" id="input-phone">
									<input type="text" name="phone" id="phone" class="form-control" placeholder="phone" value="{{$customers->phone}}">	
								</div>
							</div>
							<div class="form-group" id="form-email">
								<label class="col-md-2 control-label">Email</label>
								<div class="col-md-6" id="input-email">
									<input type="text" name="email" id="email" class="form-control" placeholder="email"  value="{{$customers->email}}">	
								</div>
							</div>
							<div class="form-group" id="form-birthdate">
								<label class="col-md-2 control-label">Birth Date</label>
								<div class="col-md-6" id="input-birthdate">
									<div class="col-md-12 input-group date" id='datepicker'>
										<input type="text" name="birthdate" id="birthdate" class="form-control" placeholder="mm-dd-yy" readonly="" value="{{$birthdate}}">
										<a class="input-group-addon" href="" id="add-category" data-toggle="modal" data-target="#modal-category">	
											<i class="fa fa-calendar"></i>									
										</a>	

									</div>
									<span><i>mm-dd-yyyy</i></span>
								</div>
							</div>
						
							<div class="form-group" id="form-address1">
								<label class="col-md-2 control-label">Address</label>
								<div class="col-md-6" id="input-address1">
									<input type="text" name="address1" id="address1" class="form-control" placeholder="address"  value="{{$customers->address1}}">	
								</div>
							</div>
							<div class="form-group" id="form-address2">
								<label class="col-md-2 control-label">Address2</label>
								<div class="col-md-6" id="input-address2">
									<input type="text" name="address2" id="address2" class="form-control" placeholder="address2"  value="{{$customers->address2}}">	
								</div>
							</div>
							<div class="form-group" id="form-city">
								<label class="col-md-2 control-label">City</label>
								<div class="col-md-6" id="input-city">
									<input type="text" name="city" id="city" class="form-control" placeholder="city"  value="{{$customers->city}}">	
								</div>
							</div>
							<div class="form-group" id="form-state">
								<label class="col-md-2 control-label">State</label>
								<div class="col-md-6" id="input-state">
									<input type="text" name="state" id="state" class="form-control" placeholder="state"  value="{{$customers->state}}">	
								</div>
							</div>
							<div class="form-group" id="form-postcode">
								<label class="col-md-2 control-label">Post Code</label>
								<div class="col-md-6" id="input-postcode">
									<input type="text" name="postcode" id="postcode" class="form-control" placeholder="post code"  value="{{$customers->postcode}}">	
								</div>
							</div>
						</div>
					</div>
					<div class="panel-footer">		
				        <button type="submit" id="btn-add-customer" class="btn btn-primary">Save</button>
				        <!-- <button type="submit" id="btn-add-create-product" class="btn btn-primary">Save and Create New</button>	 -->
				        <button type="reset" class="btn btn-secondary">Cancel</button>	
				        <button type="button" id="btn-delete" class="btn btn-danger pull-right" data-toggle="modal" data-target="#modal-delete">Delete</button>	
					</div>
				</div>	
        	</form>		
		</div>								
    </div>

    <!-- Modal Delete -->
	<div class="modal fade" id="modal-delete" tabindex="-1" role="dialog" aria-labelledby="modal-brand" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" role="document">
	    	<div class="modal-content">
	    		<form>
		      		<div class="modal-header">
		        		<h4 class="modal-title" id="exampleModalCenterTitle">Confirm Delete</h4>
		      		</div>		      		
		      		<div class="modal-body">	 				
						Are you sure you want to delete this customer?
		      		</div>
			    	<div class="modal-footer">
			    		 <a href="{{route('deletecustomer', $customerid)}}" class="btn btn-danger"><i class="fa fa-trash"></i> Delete</a>
				        <button type="button" id="btn-delete-close" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
			    	</div>	
	      		</form>
	    	</div>
		</div>
	</div>
	<!-- END Modal Delete -->

 <!-- END MAIN CONTENT -->
<script>
	var today = new Date();
   	$('.date').datepicker({  
  	   
	    format: 'mm/dd/yyyy',
	    autoclose: true,
	    maxDate: today,
	    endDate: today,

    });   

</script>

<script>

	
	$(document).ready(function(){

		$('#btn-add-customer').click(function(e){
			e.preventDefault();
			var firstname = $('#firstname').val();
			var lastname = $('#lastname').val();
			var phone = $('#phone').val();
			var email = $('#email').val();
			var birthdate = $('#birthdate').val();
			var address1 = $('#address1').val();
			var address2 = $('#address2').val();
			var city = $('#city').val();
			var state = $('#state').val();
			var postcode = $('#postcode').val();

			$.ajax({
				url: "{{route('updatecustomer')}}",
				method: "POST",
				data: {
					_token: "{{csrf_token()}}",
					customerid: {{$customerid}}, 
					firstname: firstname,
					lastname: lastname,
					phone: phone,
					email: email,
					birthdate: birthdate,
					address1: address1,
					address2: address2,
					city: city,
					state: state,
					postcode: postcode
				},
				success: function(response){
					
					$('#form-firstname').removeClass('has-error');
					$('#error-firstname').remove();
					$('#form-lastname').removeClass('has-error');
					$('#error-lastname').remove();
					
					if(response.success)
					{
						$('.alert').empty();
						$('.alert').show();
						$('.alert').append("<i class='fa fa-check'></i> "+response.success);
						$('.alert').removeClass('hide');
						$('.alert').delay(5000).fadeOut(); 	
						$('#birthdate').val(birthdate);
						$(window).scrollTop(0);
					}
					else
					{
						$('#form-firstname').addClass('has-error');
						$('#input-firstname').append("<span class='text-danger' id='error-firstname'><i>"+response['firstname']+"</i></span>");

						$('#firstname').keyup(function(e){
							e.preventDefault();
							if($('#firstname').val() == '')
							{
								$('#form-firstname').addClass('has-error');
								$('#error-firstname').remove();
								$('#input-firstname').append("<span class='text-danger' id='error-firstname'><i>This field is required</i></span>");
							}
							else
							{
								$('#form-firstname').removeClass('has-error');
								$('#error-firstname	').remove();
							}
							
						});

						$('#form-lastname').addClass('has-error');
						$('#input-lastname').append("<span class='text-danger' id='error-lastname'><i>"+response['lastname']+"</i></span>");

						$('#lastname').keyup(function(e){
							e.preventDefault();
							if($('#lastname').val() == '')
							{
								$('#form-lastname').addClass('has-error');
								$('#error-lastname').remove();
								$('#input-lastname').append("<span class='text-danger' id='error-lastname'><i>This field is required</i></span>");
							}
							else
							{
								$('#form-lastname').removeClass('has-error');
								$('#error-lastname').remove();
							}
							
						});
					}
					console.log(response);
						
				},
				error: function(response){
					console.log(response);
				}
			});

		});

	});

</script>

@endsection