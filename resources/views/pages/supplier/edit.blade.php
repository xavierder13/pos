@extends('layouts.main')

@section('main_content')
<!-- MAIN CONTENT -->

	<div class="panel">	
        <div class="panel-body">
        	<div class="content-heading clearfix">
				<div class="heading-left">
					<h1 class="page-title">Supplier</h1>
					<hr>
					<p class="page-subtitle">Edit supplier</p>
				</div>
				<ul class="breadcrumb">
					<li><a href="#"><i class="fa fa-home"></i> Home</a></li>
					<li><a href="{{route('viewsupplier')}}">Suppliers</a></li>
					<li class="active">Edit Supplier</li>
				</ul>
			</div>

			<div class="alert alert-success hide"></div>
			
        	<form id="form-supplier">
        		<div class="form-horizontal">
        			<h3 class="form-section">General</h3>
					<hr>
					<div class="row">
						<div class="col-md-12">
							<div class="form-group" id="form-suppliername">
								<label class="col-md-2 control-label">Supplier Name <span class="text-danger">*</span></label>
								<div class="col-md-6" id="input-suppliername">
									<input type="text" name="suppliername" id="suppliername" class="form-control" placeholder="supplier name" value="{{$suppliers->suppliername}}">	
								</div>
							</div>
						</div>
					</div>
					<h3 class="form-section">Contact Information</h3>
					<hr>
					<div class="row">
						<div class="col-md-12">
							<div class="form-group" id="form-contactname">
								<label class="col-md-2 control-label">Contact Name</label>
								<div class="col-md-6" id="input-contactname">
									<input type="text" name="contactname" id="contactname" class="form-control" placeholder="contact name" value="{{$suppliers->contactname}}">	
								</div>
							</div>
							<div class="form-group" id="form-phone">
								<label class="col-md-2 control-label">Phone</label>
								<div class="col-md-6" id="input-phone">
									<input type="text" name="phone" id="phone" class="form-control" placeholder="phone" value="{{$suppliers->phone}}">	
								</div>
							</div>
							<div class="form-group" id="form-email">
								<label class="col-md-2 control-label">Email</label>
								<div class="col-md-6" id="input-email">
									<input type="text" name="email" id="email" class="form-control" placeholder="email" value="{{$suppliers->email}}">	
								</div>
							</div>
							<div class="form-group" id="form-fax">
								<label class="col-md-2 control-label">Fax</label>
								<div class="col-md-6" id="input-fax">
									<input type="text" name="fax" id="fax" class="form-control" placeholder="fax" value="{{$suppliers->fax}}">	
								</div>
							</div>
							<div class="form-group" id="form-website">
								<label class="col-md-2 control-label">Website</label>
								<div class="col-md-6" id="input-website">
									<input type="text" name="website" id="website" class="form-control" placeholder="website" value="{{$suppliers->website}}">
								</div>
							</div>
						</div>
					</div>
					<h3 class="form-section">Address</h3>
					<hr>
					<div class="row">
						<div class="col-md-12">
							<div class="form-group" id="form-street">
								<label class="col-md-2 control-label">Street</label>
								<div class="col-md-6" id="input-street">
									<input type="text" name="street" id="street" class="form-control" placeholder="street" value="{{$suppliers->street}}">	
								</div>
							</div>
							<div class="form-group" id="form-city">
								<label class="col-md-2 control-label">City</label>
								<div class="col-md-6" id="input-city">
									<input type="text" name="city" id="city" class="form-control" placeholder="city" value="{{$suppliers->city}}">	
								</div>
							</div>
							<div class="form-group" id="form-state">
								<label class="col-md-2 control-label">State</label>
								<div class="col-md-6" id="input-state">
									<input type="text" name="state" id="state" class="form-control" placeholder="state" value="{{$suppliers->state}}">	
								</div>
							</div>
							<div class="form-group" id="form-postcode">
								<label class="col-md-2 control-label">Post Code</label>
								<div class="col-md-6" id="input-postcode">
									<input type="text" name="postcode" id="postcode" class="form-control" placeholder="post code" value="{{$suppliers->postcode}}">	
								</div>
							</div>
							<div class="form-group" id="form-country">
								<label class="col-md-2 control-label">Country</label>
								<div class="col-md-6" id="input-country">
									<div class="col-md-12 input-group">
										<select class="form-control" name="country" id="country">
											<option selected="" disabled="" > (No Country) </option>
											@foreach($countries as $country)
											<option value="{{$country->name->common}}" id="{{$country->name->common}}" 
											@if($suppliers->country == $country->name->common)
												selected="" 
											@endif	id> {{$country->name->common}} </option>
											@endforeach
										</select>			
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="panel-footer">		
				        <button type="submit" id="btn-add-supplier" class="btn btn-primary">Save</button>
				        <!-- <button type="submit" id="btn-add-create-product" class="btn btn-primary">Save and Create New</button>	 -->
				        <button type="reset" class="btn btn-secondary">Cancel</button>	
				        <button type="button" id="btn-delete" class="btn btn-danger pull-right" data-toggle="modal" data-target="#modal-delete">Delete</button>	
					</div>
				</div>	
        	</form>		
		</div>								
    </div>

<!-- END MAIN CONTENT -->
<!-- Modal Delete -->
	<div class="modal fade" id="modal-delete" tabindex="-1" role="dialog" aria-labelledby="modal-brand" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" role="document">
	    	<div class="modal-content">
	    		<form>
		      		<div class="modal-header">
		        		<h4 class="modal-title" id="exampleModalCenterTitle">Confirm Delete</h4>
		      		</div>		      		
		      		<div class="modal-body">	 				
						Are you sure you want to delete this supplier?
		      		</div>
			    	<div class="modal-footer">
			    		 <a href="{{route('deletesupplier', $supplierid)}}" class="btn btn-danger"><i class="fa fa-trash"></i> Delete</a>
				        <button type="button" id="btn-delete-close" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
			    	</div>	
	      		</form>
	    	</div>
		</div>
	</div>
	<!-- END Modal Delete -->
<script>
	
	$(document).ready(function(){

		$('#btn-add-supplier').click(function(e){
			e.preventDefault();
			var suppliername = $('#suppliername').val();
			var contactname = $('#contactname').val();
			var phone = $('#phone').val();
			var email = $('#email').val();
			var fax = $('#fax').val();
			var website = $('#website').val();
			var street = $('#street').val();
			var city = $('#city').val();
			var state = $('#state').val();
			var postcode = $('#postcode').val();
			var country = $('#country').val();

			$.ajax({
				url: "{{route('updatesupplier')}}",
				method: "POST",
				data: {
					_token: "{{csrf_token()}}",
					supplierid: {{$supplierid}},
					suppliername: suppliername,
					contactname: contactname,
					phone: phone,
					email: email,
					fax: fax,
					website: website,
					street: street,
					city: city,
					state: state,
					postcode: postcode,
					country: country
				},
				success: function(response){
					
					$('#form-suppliername').removeClass('has-error');
					$('#error-suppliername').remove();
					
					if(response.success)
					{
						$('.alert').empty();
						$('.alert').show();
						$('.alert').append("<i class='fa fa-check'></i> "+response.success);
						$('.alert').removeClass('hide');
						$('.alert').delay(5000).fadeOut(); 
						$(window).scrollTop(0);
						$('#'+country).prop('selected', 'selected');
						alert(country);


					}
					else
					{
						$('#form-suppliername').addClass('has-error');
						$('#input-suppliername').append("<span class='text-danger' id='error-suppliername'><i>"+response['suppliername']+"</i></span>");

						$('#suppliername').keyup(function(e){
							e.preventDefault();
							if($('#suppliername').val() == '')
							{
								$('#form-suppliername').addClass('has-error');
								$('#error-suppliername').remove();
								$('#input-suppliername').append("<span class='text-danger' id='error-suppliername'><i>This field is required</i></span>");
							}
							else
							{
								$('#form-suppliername').removeClass('has-error');
								$('#error-suppliername').remove();
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