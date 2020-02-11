@extends('layouts.main')
@section('main_content')
<!-- MAIN CONTENT -->

	<div class="panel">	
        <div class="panel-body">
        	<div class="content-heading clearfix">
				<div class="heading-left">
					<h1 class="page-title">Supplier</h1>
					<hr>
					<!-- <p class="page-subtitle">Create new product</p> -->
				</div>
				<ul class="breadcrumb">
					<li><a href="#"><i class="fa fa-home"></i> Home</a></li>
					<li class="active"><a href="#">Supplier</a></li>
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
					<a href="{{route('createsupplier')}}" class="btn btn-primary"><i class="fa fa-plus"></i> Add Supplier</a>
					<br><br>
				</div>
				<div class="row">
					<div class="col-md-12">
						<table id="supplier-table" class="table table-striped table-bordered">
						    <thead>
						        <tr>
						            <th>Supplier Name</th>
						            <th>Email</th>
						            <th>Phone</th>
						        </tr>
						    </thead>
						    <tbody>
						      	@foreach($suppliers as $supplier)
						        <tr>
						            <td><a href="{{route('editsupplier',['supplierid' => $supplier->id])}}">{{$supplier->suppliername}}</a></td>
						            <td>{{$supplier->email}}</td>
						            <td>{{$supplier->phone}}</td>
						        </tr>
						        @endforeach
						            
						    </tbody>
						    <tfoot>
						        <tr>
						            <th>Supplier Name</th>
						            <th>Email</th>
						            <th>Phone</th>
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

