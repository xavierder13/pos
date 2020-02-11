@extends('layouts.main')

@section('main_content')
<!-- MAIN CONTENT -->
	<div class="panel">	
        <div class="panel-body">
        	<div class="content-heading clearfix">
				<div class="heading-left">
					<h1 class="page-title">Purchase Orders</h1>
					<hr>
					<!-- <p class="page-subtitle">Create new product</p> -->
				</div>
				<ul class="breadcrumb">
					<li><a href="#"><i class="fa fa-home"></i> Home</a></li>
					<li class="active"><a href="#">Purchase Orders</a></li>
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
					<a href="" class="btn btn-primary"><i class="fa fa-plus"></i> New Purchase Order</a>
					<br><br>
				</div>
				<div class="row">
					<div class="col-md-12">
						<table id="po-table" class="table table-striped table-bordered">
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
						      	<tr>
						      		<td>
						      			<div id="prodcode">
							      			<select class="form-control selectpicker" name="product" id="product" data-live-search="true">
							      				<option selected="">(Select Country)</option>
							                	<option id="opt" data-tokens="china">China</option>
												<option id="opt" data-tokens="malayasia">Malayasia</option>
												<option id="opt" data-tokens="singapore">Singapore</option>
							                </select>
						                </div>
			            			</td>
						            <td>Item Name</td>
						            <td>Category</td>
						            <td>Brand</td>
						            <td>Cost (PHP)</td>
						            <td>Price (PHP)</td>
						            <td>Quantity</td>

						      	</tr>  
						      	<tr>
						      		<td>
						      			<div id="prodcode">
							      			<select class="form-control selectpicker" name="product" id="product" data-live-search="true">
							                	<option id="opt" data-tokens="china">China</option>
												<option id="opt" data-tokens="malayasia">Malayasia</option>
												<option id="opt" data-tokens="singapore">Singapore</option>
							                </select>
						                </div>
			            			</td>
						            <td>Item Name</td>
						            <td>Category</td>
						            <td>Brand</td>
						            <td>Cost (PHP)</td>
						            <td>Price (PHP)</td>
						            <td>Quantity</td>
						            
						      	</tr>      
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

		// $('.selectpicker').selectpicker();

		$('.btn').click(function(e){
			e.preventDefault();

			$('#po-table tbody').append('<tr>'+
											'<td>'+
													
									      			'<select class="form-control selectpicker" name="product" id="product2" data-live-search="true">'+
							                	'<option id="opt" data-tokens="china">China</option>'+
												'<option id="opt" data-tokens="malayasia">Malayasia</option>'+
												'<option id="opt" data-tokens="singapore">Singapore</option>'+
							                '</select>'+
							                	
						                	'</td>'+
						            		'<td>Item Name</td>'+
						            		'<td>Category</td>'+
						            		'<td>Brand</td>'+
						            		'<td>Cost (PHP)</td>'+
						            		'<td>Price (PHP)</td>'+
						            		'<td>Quantity</td>'+
						            	'</tr>');
			// $('#prodcode').append('<select class="form-control selectpicker" name="product" id="product" data-live-search="true">'+
			// 				                	'<option id="opt" data-tokens="china">China</option>'+
			// 									'<option id="opt" data-tokens="malayasia">Malayasia</option>'+
			// 									'<option id="opt" data-tokens="singapore">Singapore</option>'+
			// 				                '</select>');
			$('.selectpicker').selectpicker();
			

			
			
		});

		$("#po-table tbody").each(function(e){
			var select = $(this).find("select");
			var button = $(this).find("button");
			var div = $(this).find("div");

			$(this).on('click','tr td',function(e){
				  	var col = $(this).parent().children().index($(this));
					var row = $(this).parent().parent().children().index($(this).parent());
					alert('Row: ' + row + ', Column: ' + col);

					$(this).find('td:eq(2)').append('asdsadasd');
					// div.empty();
				 //    div.append('asdasdasda');
				 //    alert('sds');
			});

			    
			    
		});



	});
</script>


@endsection