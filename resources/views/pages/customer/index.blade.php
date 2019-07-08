@extends('layouts.main')
@section('main_content')
<!-- MAIN CONTENT -->

	<div class="panel">	
        <div class="panel-body">
        	<div class="content-heading clearfix">
				<div class="heading-left">
					<h1 class="page-title">Customer</h1>
					<hr>
					<!-- <p class="page-subtitle">Create new product</p> -->
				</div>
				<ul class="breadcrumb">
					<li><a href="#"><i class="fa fa-home"></i> Home</a></li>
					<li class="active"><a href="#">Customer</a></li>
				</ul>
			</div>
			@if(session()->get('success'))
			    <div class="alert alert-success">
			    	<i class="fa fa-check"></i>
			    	{{ session()->get('success') }}  
			    </div>
			@endif
        	<div class="form-horizontal">
        		<div class="pull-right">
					<a href="{{route('createcustomer')}}" class="btn btn-primary"><i class="fa fa-plus"></i> Add Customer</a>
					<br><br>
				</div>
				<div class="row">
					<div class="col-md-12">
						<table id="supplier-table" class="table table-striped table-bordered">
						    <thead>
						        <tr>
						            <th>First Name</th>
						            <th>Last Name</th>
						            <th>Phone</th>
						            <th>Email</th>
						            <th>Address</th>
						            <th>City</th>
						            <th>State</th>
						        </tr>
						    </thead>
						    <tbody>
						      	@foreach($customers as $customer)
						        <tr>
						            <td><a href="{{route('editcustomer',['customerid' => $customer->id])}}">{{$customer->firstname}}</a></td>
						            <td>{{$customer->lastname}}</td>
						            <td>{{$customer->phone}}</td>
						            <td>{{$customer->email}}</td>
						            <td>{{$customer->address1}}</td>
						            <td>{{$customer->city}}</td>
						            <td>{{$customer->state}}</td>
						        </tr>
						        @endforeach
						            
						    </tbody>
						    <tfoot>
						        <tr>
						            <th>First Name</th>
						            <th>Last Name</th>
						            <th>Phone</th>
						            <th>Email</th>
						            <th>Address</th>
						            <th>City</th>
						            <th>State</th>
						        </tr>
						    </tfoot>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>

<script>
	$(document).ready(function(){		
		$('.alert').delay(5000).fadeOut();		
	});
</script>
@endsection


@section('datatable')
<script>
	$(document).ready(function() {
		$('#supplier-table').DataTable();
	    // $('#product-table').DataTable({
	    // 	"columnDefs": [{
	    // 		"targets": 'no-sort',
        //    	"orderable": false
	    // 	}]
	    // });
	});
</script>
@endsection

