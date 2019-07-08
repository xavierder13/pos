@extends('layouts.main')
@section('main_content')
<!-- MAIN CONTENT -->

	<div class="panel">	
        <div class="panel-body">
        	<div class="content-heading clearfix">
				<div class="heading-left">
					<h1 class="page-title">Product</h1>
					<hr>
					<!-- <p class="page-subtitle">Create new product</p> -->
				</div>
				<ul class="breadcrumb">
					<li><a href="#"><i class="fa fa-home"></i> Home</a></li>
					<li class="active"><a href="#">Products</a></li>
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
					<a href="{{route('createproduct')}}" class="btn btn-primary"><i class="fa fa-plus"></i> Add Product</a>
					<br><br>
				</div>
				<div class="row">
					<div class="col-md-12">
						<table id="product-table" class="table table-striped table-bordered">
						    <thead>
						        <tr>
						            <th>Item Code</th>
						            <th>Item Name</th>
						            <th>Category</th>
						            <th>Brand</th>
						            <th>Cost (PHP)</th>
						            <th>Price (PHP)</th>
						            <th>Quantity</th>
						        </tr>
						    </thead>
						    <tbody>
						      	@foreach($products as $product)
						        <tr>
						            <td><a href="{{route('editproduct',['productid' => $product->id])}}">{{$product->itemcode}}</a></td>
						            <td>{{$product->itemname}}</td>
						            <td>{{$product->category}}</td>
						            <td>{{$product->brand}}</td>
						            <td>{{$product->avgprice}}</td>
						            <td>{{$product->srp}}</td>
						            <td>{{$product->onhand}}</td>
						        </tr>
						        @endforeach
						            
						    </tbody>
						    <tfoot>
						        <tr>
						            <th>Item Code</th>
						            <th>Item Name</th>
						            <th>Category</th>
						            <th>Brand</th>
						            <th>Cost (PHP)</th>
						            <th>Price (PHP)</th>
						            <th>Quantity</th>
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
		$('#product-table').DataTable();
	    // $('#product-table').DataTable({
	    // 	"columnDefs": [{
	    // 		"targets": 'no-sort',
        //    	"orderable": false
	    // 	}]
	    // });
	});
</script>
@endsection

