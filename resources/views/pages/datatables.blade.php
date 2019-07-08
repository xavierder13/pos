<!doctype html>
<html lang="en">
	<head>
		<title>Dynamic Tables | Klorofil Pro - Bootstrap Admin Dashboard Template</title>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
		<!-- VENDOR CSS -->
		<link rel="stylesheet" href="assets/vendor/bootstrap/css/bootstrap.min.css">
		<link rel="stylesheet" href="assets/vendor/font-awesome/css/font-awesome.min.css">
		<link rel="stylesheet" href="assets/vendor/themify-icons/css/themify-icons.css">
		<link rel="stylesheet" href="assets/vendor/pace/themes/orange/pace-theme-minimal.css">
		<link rel="stylesheet" href="assets/vendor/datatables/css-main/jquery.dataTables.min.css">
		<link rel="stylesheet" href="assets/vendor/datatables/css-bootstrap/dataTables.bootstrap.min.css">
		<link rel="stylesheet" href="assets/vendor/datatables-tabletools/css/dataTables.tableTools.css">
		<!-- MAIN CSS -->
		<link rel="stylesheet" href="assets/css/main.min.css">
		<link rel="stylesheet" href="assets/css/skins/sidebar-nav-darkgray.css" type="text/css">
		<link rel="stylesheet" href="assets/css/skins/navbar3.css" type="text/css">
		<!-- FOR DEMO PURPOSES ONLY. You should/may remove this in your project -->
		<link rel="stylesheet" href="assets/css/demo.min.css">
		<link rel="stylesheet" href="demo-panel/style-switcher.css">
		<!-- ICONS -->
		<link rel="apple-touch-icon" sizes="76x76" href="assets/img/apple-icon.png">
		<link rel="icon" type="image/png" sizes="96x96" href="assets/img/favicon.png">
	</head>
	<body>
		<!-- WRAPPER -->
		<div id="wrapper">
			<!-- NAVBAR -->
			<nav class="navbar navbar-default navbar-fixed-top">
				<div class="brand">
					<a href="index.html">
						<img src="assets/img/logo-white.png" alt="Klorofil Pro Logo" class="img-responsive logo">
					</a>
				</div>
				<div class="container-fluid">
					<div id="tour-fullwidth" class="navbar-btn">
						<button type="button" class="btn-toggle-fullwidth"><i class="ti-arrow-circle-left"></i></button>
					</div>
					<form class="navbar-form navbar-left search-form">
						<input type="text" value="" class="form-control" placeholder="Search dashboard...">
						<button type="button" class="btn btn-default"><i class="fa fa-search"></i></button>
					</form>
					<div id="navbar-menu">
						<ul class="nav navbar-nav navbar-right">
							<li>
								<a href="#" class="btn-toggle-rightsidebar">
									<i class="ti-layout-sidebar-right"></i>
								</a>
							</li>
							<li class="dropdown">
								<a href="#" class="dropdown-toggle icon-menu" data-toggle="dropdown">
									<i class="ti-bell"></i>
									<span class="badge bg-danger">5</span>
								</a>
								<ul class="dropdown-menu notifications">
									<li>You have 5 new notifications</li>
									<li>
										<a href="#" class="notification-item">
											<i class="fa fa-hdd-o custom-bg-red"></i>
											<p>
												<span class="text">System space is almost full</span>
												<span class="timestamp">11 minutes ago</span>
											</p>
										</a>
									</li>
									<li>
										<a href="#" class="notification-item">
											<i class="fa fa-tasks custom-bg-yellow"></i>
											<p>
												<span class="text">You have 9 unfinished tasks</span>
												<span class="timestamp">20 minutes ago</span>
											</p>
										</a>
									</li>
									<li>
										<a href="#" class="notification-item">
											<i class="fa fa-book custom-bg-green2"></i>
											<p>
												<span class="text">Monthly report is available</span>
												<span class="timestamp">1 hour ago</span>
											</p>
										</a>
									</li>
									<li>
										<a href="#" class="notification-item">
											<i class="fa fa-bullhorn custom-bg-purple"></i>
											<p>
												<span class="text">Weekly meeting in 1 hour</span>
												<span class="timestamp">2 hours ago</span>
											</p>
										</a>
									</li>
									<li>
										<a href="#" class="notification-item">
											<i class="fa fa-check custom-bg-green"></i>
											<p>
												<span class="text">Your request has been approved</span>
												<span class="timestamp">3 days ago</span>
											</p>
										</a>
									</li>
									<li><a href="#" class="more">See all notifications</a></li>
								</ul>
							</li>
							<li class="dropdown">
								<a href="#" id="tour-help" class="dropdown-toggle" data-toggle="dropdown"><i class="ti-help"></i> <span class="hide">Help</span></a>
								<ul class="dropdown-menu">
									<li><a href="#"><i class="ti-direction"></i> Basic Use</a></li>
									<li><a href="#"><i class="ti-server"></i> Working With Data</a></li>
									<li><a href="#"><i class="ti-lock"></i> Security</a></li>
									<li><a href="#"><i class="ti-light-bulb"></i> Troubleshooting</a></li>
								</ul>
							</li>
							<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown">
									<img src="assets/img/user.png" alt="Avatar">
									<span>Samuel</span>
								</a>
								<ul class="dropdown-menu logged-user-menu">
									<li><a href="#"><i class="ti-user"></i> <span>My Profile</span></a></li>
									<li><a href="appviews-inbox.html"><i class="ti-email"></i> <span>Message</span></a></li>
									<li><a href="#"><i class="ti-settings"></i> <span>Settings</span></a></li>
									<li><a href="page-lockscreen.html"><i class="ti-power-off"></i> <span>Logout</span></a></li>
								</ul>
							</li>
						</ul>
					</div>
				</div>
			</nav>
			<!-- END NAVBAR -->
			<!-- LEFT SIDEBAR -->
			<div id="sidebar-nav" class="sidebar">
				<nav>
					<ul class="nav" id="sidebar-nav-menu">
						<li class="menu-group">Main</li>
						<li class="panel">
							<a href="#dashboards" data-toggle="collapse" data-parent="#sidebar-nav-menu" class="collapsed"><i class="ti-dashboard"></i> <span class="title">Dashboards</span> <i class="icon-submenu ti-angle-left"></i></a>
							<div id="dashboards" class="collapse ">
								<ul class="submenu">
									<li><a href="index.html">Dashboard v1 <span class="label label-info">UPDATED</span></a></li>
									<li><a href="dashboard2.html">Dashboard v2</a></li>
									<li><a href="dashboard3.html">Dashboard v3</a></li>
									<li><a href="dashboard4.html">Dashboard v4 <span class="label label-success">NEW</span></a></li>
								</ul>
							</div>
						</li>
						<li class="panel">
							<a href="#subLayouts" data-toggle="collapse" data-parent="#sidebar-nav-menu" class="collapsed"><i class="ti-layout"></i> <span class="title">Layouts</span> <i class="icon-submenu ti-angle-left"></i></a>
							<div id="subLayouts" class="collapse ">
								<ul class="submenu">
									<li><a href="layout-topnav.html">Top Navigation <span class="label label-success">NEW</span></a></li>
									<li><a href="layout-minified.html">Minified</a></li>
									<li><a href="layout-fullwidth.html">Fullwidth</a></li>
									<li><a href="layout-default.html">Default</a></li>
									<li><a href="layout-grid.html">Grid</a></li>
								</ul>
							</div>
						</li>
						<li class="panel">
							<a href="#forms" data-toggle="collapse" data-parent="#sidebar-nav-menu" class="collapsed"><i class="ti-receipt"></i> <span class="title">Forms</span> <i class="icon-submenu ti-angle-left"></i></a>
							<div id="forms" class="collapse ">
								<ul class="submenu">
									<li><a href="forms-inputs.html">Inputs</a></li>
									<li><a href="forms-multiselect.html">Multiselect</a></li>
									<li><a href="forms-input-pickers.html">Input Pickers</a></li>
									<li><a href="forms-input-sliders.html">Input Sliders</a></li>
									<li><a href="forms-select2.html">Select2</a></li>
									<li><a href="forms-xeditable.html">In-place Editing</a></li>
									<li><a href="forms-dragdropupload.html">Drag and Drop Upload</a></li>
									<li><a href="forms-layouts.html">Form Layouts</a></li>
									<li><a href="forms-validation.html">Form Validation</a></li>
									<li><a href="forms-texteditor.html">Text Editor</a></li>
								</ul>
							</div>
						</li>
						<li class="panel">
							<a href="#appViews" data-toggle="collapse" data-parent="#sidebar-nav-menu" class="collapsed"><i class="ti-layout-tab-window"></i> <span class="title">App Views</span> <i class="icon-submenu ti-angle-left"></i></a>
							<div id="appViews" class="collapse ">
								<ul class="submenu">
									<li><a href="appviews-project-detail.html">Project Details</a></li>
									<li><a href="appviews-projects.html">Projects</a></li>
									<li><a href="appviews-inbox.html">Inbox <span class="badge">8</span></a></li>
									<li><a href="appviews-file-manager.html">File Manager</a></li>
								</ul>
							</div>
						</li>
						<li class="panel">
							<a href="#tables" data-toggle="collapse" data-parent="#sidebar-nav-menu" class="active"><i class="ti-layout-grid2"></i> <span class="title">Tables</span> <i class="icon-submenu ti-angle-left"></i></a>
							<div id="tables" class="collapse in">
								<ul class="submenu">
									<li><a href="tables-static.html">Static Tables</a></li>
									<li><a href="tables-dynamic.html" class="active">Dynamic Tables</a></li>
								</ul>
							</div>
						</li>
						<li class="menu-group">Components</li>
						<li class="panel">
							<a href="#uiElements" data-toggle="collapse" data-parent="#sidebar-nav-menu" class="collapsed"><i class="ti-panel"></i> <span class="title">UI Elements</span> <i class="icon-submenu ti-angle-left"></i></a>
							<div id="uiElements" class="collapse ">
								<ul class="submenu">
									<li><a href="ui-sweetalert.html">Sweet Alert</a></li>
									<li><a href="ui-treeview.html">Tree View</a></li>
									<li><a href="ui-wizard.html">Wizard</a></li>
									<li><a href="ui-dragdrop-panel.html">Drag &amp; Drop Panel</a></li>
									<li><a href="ui-nestable.html">Nestable</a></li>
									<li><a href="ui-gauge.html">Gauge <span class="label label-success">NEW</span></a></li>
									<li><a href="ui-panels.html">Panels</a></li>
									<li><a href="ui-progressbars.html">Progress Bars</a></li>
									<li><a href="ui-tabs.html">Tabs</a></li>
									<li><a href="ui-buttons.html">Buttons <span class="label label-info">UPDATED</span></a></li>
									<li><a href="ui-bootstrap.html">Bootstrap UI</a></li>
									<li><a href="ui-social-buttons.html">Social Buttons</a></li>
									<li><a href="ui-icons.html">Icons</a></li>
								</ul>
							</div>
						</li>
						<li class="panel">
							<a href="#charts" data-toggle="collapse" data-parent="#sidebar-nav-menu" class="collapsed"><i class="ti-pie-chart"></i> <span class="title">Charts</span> <i class="icon-submenu ti-angle-left"></i></a>
							<div id="charts" class="collapse ">
								<ul class="submenu">
									<li><a href="charts-chartjs.html">Chart.js</a></li>
									<li><a href="charts-chartist.html">Chartist</a></li>
									<li><a href="charts-flot.html">Flot Chart</a></li>
									<li><a href="charts-sparkline.html">Sparkline Chart</a></li>
								</ul>
							</div>
						</li>
						<li><a href="widgets.html"><i class="ti-widget"></i> <span class="title">Widgets</span></a></li>
						<li><a href="notifications.html"><i class="ti-bell"></i> <span class="title">Notifications</span> <span class="badge">15</span></a></li>
						<li class="panel">
							<a href="#maps" data-toggle="collapse" data-parent="#sidebar-nav-menu" class="collapsed"><i class="ti-map"></i> <span class="title">Maps</span> <i class="icon-submenu ti-angle-left"></i></a>
							<div id="maps" class="collapse ">
								<ul class="submenu">
									<li><a href="maps-jqvmap.html">JQVMap</a></li>
									<li><a href="maps-mapael.html">Mapael</a></li>
								</ul>
							</div>
						</li>
						<li class="menu-group">Extras</li>
						<li class="panel">
							<a href="#subPages" data-toggle="collapse" data-parent="#sidebar-nav-menu" class="collapsed"><i class="ti-files"></i> <span class="title">Pages</span> <i class="icon-submenu ti-angle-left"></i></a>
							<div id="subPages" class="collapse ">
								<ul class="submenu">
									<li><a href="page-profile.html">Profile</a></li>
									<li><a href="page-login.html">Login</a></li>
									<li><a href="page-register.html">Register</a></li>
									<li><a href="page-lockscreen.html">Lockscreen</a></li>
									<li><a href="page-forgot-password.html">Forgot Password</a></li>
									<li><a href="page-404.html">Page 404</a></li>
									<li><a href="page-500.html">Page 500</a></li>
									<li><a href="page-blank.html">Blank Page</a></li>
								</ul>
							</div>
						</li>
						<li><a href="typography.html"><i class="ti-paragraph"></i> <span class="title">Typography</span></a></li>
						<li class="panel">
							<a href="#" data-toggle="collapse" data-target="#submenuDemo" data-parent="#sidebar-nav-menu" class="collapsed"><i class="ti-menu"></i> <span class="title">Multilevel Menu</span><i class="icon-submenu ti-angle-left"></i></a>
							<div id="submenuDemo" class="collapse">
								<ul class="submenu">
									<li class="panel">
										<a href="#" data-toggle="collapse" data-target="#submenuDemoLv2" class="collapsed">Submenu 1 <i class="icon-submenu ti-angle-left"></i></a>
										<div id="submenuDemoLv2" class="collapse">
											<ul class="submenu">
												<li><a href="#">Another menu level</a></li>
												<li><a href="#" class="active">Another menu level</a></li>
												<li><a href="#">Another menu level</a></li>
											</ul>
										</div>
									</li>
									<li><a href="#">Submenu 2</a></li>
									<li><a href="#">Submenu 3</a></li>
								</ul>
							</div>
						</li>
					</ul>
					<button type="button" class="btn-toggle-minified" title="Toggle Minified Menu"><i class="ti-arrows-horizontal"></i></button>
				</nav>
			</div>
			<!-- END LEFT SIDEBAR -->
			<!-- MAIN -->
			<div class="main">
				<!-- MAIN CONTENT -->
				<div class="main-content">
					<div class="content-heading clearfix">
						<div class="heading-left">
							<h1 class="page-title">Dynamic Tables</h1>
							<p class="page-subtitle">Tables with powerful features such as sorting, drag and drop column, filter and more.</p>
						</div>
						<ul class="breadcrumb">
							<li><a href="#"><i class="fa fa-home"></i> Home</a></li>
							<li><a href="#">Tables</a></li>
							<li class="active">Dynamic Tables</li>
						</ul>
					</div>
					<div class="container-fluid">
						<!-- DRAG/DROP COLUMNS REORDER -->
						<div class="panel">
							<div class="panel-heading">
								<h3 class="panel-title">Drag/Drop Column Reorder</h3>
							</div>
							<div class="panel-body">
								<p class="alert alert-info"><i class="fa fa-info-circle"></i> Try drag and drop the column to another position to reorder table columns.</p>
								<table id="datatable-column-reorder" class="table table-hover table-bordered">
									<thead>
										<tr>
											<th>Browser</th>
											<th>Operating System</th>
											<th>Visits</th>
											<th>New Visits</th>
											<th>Bounce Rate</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>Chrome</td>
											<td>Macintosh</td>
											<td>360</td>
											<td>82.78%</td>
											<td>87.77%</td>
										</tr>
										<tr>
											<td>Chrome</td>
											<td>Windows</td>
											<td>582</td>
											<td>87.24%</td>
											<td>90.12%</td>
										</tr>
										<tr>
											<td>Chrome</td>
											<td>Linux</td>
											<td>172</td>
											<td>45.21%</td>
											<td>48.81%</td>
										</tr>
										<tr>
											<td>Chrome</td>
											<td>iOS</td>
											<td>86</td>
											<td>35.23%</td>
											<td>44.21%</td>
										</tr>
										<tr>
											<td>Firefox</td>
											<td>Windows</td>
											<td>280</td>
											<td>63.12%</td>
											<td>89.34%</td>
										</tr>
										<tr>
											<td>Firefox</td>
											<td>Android</td>
											<td>236</td>
											<td>58.02%</td>
											<td>76.19%</td>
										</tr>
										<tr>
											<td>Internet Explorer</td>
											<td>Windows</td>
											<td>145</td>
											<td>45.23%</td>
											<td>94.65%</td>
										</tr>
										<tr>
											<td>Opera</td>
											<td>Windows</td>
											<td>328</td>
											<td>67.12%</td>
											<td>78.34%</td>
										</tr>
										<tr>
											<td>Opera</td>
											<td>Macintosh</td>
											<td>22</td>
											<td>87.21%</td>
											<td>79.81%</td>
										</tr>
										<tr>
											<td>Chrome</td>
											<td>iOS</td>
											<td>45</td>
											<td>23.21%</td>
											<td>34.67%</td>
										</tr>
										<tr>
											<td>Chrome</td>
											<td>Linux</td>
											<td>142</td>
											<td>46.61%</td>
											<td>49.72%</td>
										</tr>
										<tr>
											<td>Chrome</td>
											<td>iOS</td>
											<td>96</td>
											<td>45.43%</td>
											<td>46.11%</td>
										</tr>
										<tr>
											<td>Firefox</td>
											<td>Windows</td>
											<td>235</td>
											<td>23.42%</td>
											<td>77.44%</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
						<!-- END DRAG/DROP COLUMNS REORDER -->
						<!-- FILTER COLUMN -->
						<div class="panel">
							<div class="panel-heading">
								<h3 class="panel-title">Column Filter Enabled</h3>
							</div>
							<div class="panel-body">
								<table id="datatable-column-filter" class="table table-striped table-hover">
									<thead>
										<tr>
											<th>Browser</th>
											<th>Operating System</th>
											<th>Visits</th>
											<th>New Visits</th>
											<th>Bounce Rate</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>Chrome</td>
											<td>Macintosh</td>
											<td>360</td>
											<td>82.78%</td>
											<td>87.77%</td>
										</tr>
										<tr>
											<td>Chrome</td>
											<td>Windows</td>
											<td>582</td>
											<td>87.24%</td>
											<td>90.12%</td>
										</tr>
										<tr>
											<td>Chrome</td>
											<td>Linux</td>
											<td>172</td>
											<td>45.21%</td>
											<td>48.81%</td>
										</tr>
										<tr>
											<td>Chrome</td>
											<td>iOS</td>
											<td>86</td>
											<td>35.23%</td>
											<td>44.21%</td>
										</tr>
										<tr>
											<td>Firefox</td>
											<td>Windows</td>
											<td>280</td>
											<td>63.12%</td>
											<td>89.34%</td>
										</tr>
										<tr>
											<td>Firefox</td>
											<td>Android</td>
											<td>236</td>
											<td>58.02%</td>
											<td>76.19%</td>
										</tr>
										<tr>
											<td>Internet Explorer</td>
											<td>Windows</td>
											<td>145</td>
											<td>45.23%</td>
											<td>94.65%</td>
										</tr>
										<tr>
											<td>Safari</td>
											<td>Macintosh</td>
											<td>103</td>
											<td>22.12%</td>
											<td>52.43%</td>
										</tr>
										<tr>
											<td>Safari</td>
											<td>iOS</td>
											<td>302</td>
											<td>56.98%</td>
											<td>45.53%</td>
										</tr>
										<tr>
											<td>Opera</td>
											<td>Windows</td>
											<td>328</td>
											<td>67.12%</td>
											<td>78.34%</td>
										</tr>
										<tr>
											<td>Opera</td>
											<td>Macintosh</td>
											<td>22</td>
											<td>87.21%</td>
											<td>79.81%</td>
										</tr>
										<tr>
											<td>Chrome</td>
											<td>iOS</td>
											<td>45</td>
											<td>23.21%</td>
											<td>34.67%</td>
										</tr>
										<tr>
											<td>Firefox</td>
											<td>Windows</td>
											<td>67</td>
											<td>27.11%</td>
											<td>78.08%</td>
										</tr>
										<tr>
											<td>Chrome</td>
											<td>Macintosh</td>
											<td>120</td>
											<td>80.78%</td>
											<td>80.77%</td>
										</tr>
										<tr>
											<td>Chrome</td>
											<td>Windows</td>
											<td>682</td>
											<td>89.44%</td>
											<td>95.32%</td>
										</tr>
										<tr>
											<td>Chrome</td>
											<td>Windows</td>
											<td>172</td>
											<td>43.11%</td>
											<td>46.89%</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
						<!-- END FILTER COLUMN -->
						<!-- FEATURED DATATABLE -->
						<div class="panel">
							<div class="panel-heading">
								<h3 class="panel-title">Featured Datatable</h3>
							</div>
							<div class="panel-body">
								<p class="alert alert-info">Added paging options and live search</p>
								<table id="featured-datatable" class="table table-striped table-hover">
									<thead>
										<tr>
											<th>Browser</th>
											<th>Operating System</th>
											<th>Visits</th>
											<th>New Visits</th>
											<th>Bounce Rate</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>Chrome</td>
											<td>Macintosh</td>
											<td>360</td>
											<td>82.78%</td>
											<td>87.77%</td>
										</tr>
										<tr>
											<td>Chrome</td>
											<td>Windows</td>
											<td>582</td>
											<td>87.24%</td>
											<td>90.12%</td>
										</tr>
										<tr>
											<td>Chrome</td>
											<td>Linux</td>
											<td>172</td>
											<td>45.21%</td>
											<td>48.81%</td>
										</tr>
										<tr>
											<td>Chrome</td>
											<td>iOS</td>
											<td>86</td>
											<td>35.23%</td>
											<td>44.21%</td>
										</tr>
										<tr>
											<td>Firefox</td>
											<td>Windows</td>
											<td>280</td>
											<td>63.12%</td>
											<td>89.34%</td>
										</tr>
										<tr>
											<td>Firefox</td>
											<td>Android</td>
											<td>236</td>
											<td>58.02%</td>
											<td>76.19%</td>
										</tr>
										<tr>
											<td>Internet Explorer</td>
											<td>Windows</td>
											<td>145</td>
											<td>45.23%</td>
											<td>94.65%</td>
										</tr>
										<tr>
											<td>Safari</td>
											<td>Macintosh</td>
											<td>103</td>
											<td>22.12%</td>
											<td>52.43%</td>
										</tr>
										<tr>
											<td>Safari</td>
											<td>iOS</td>
											<td>302</td>
											<td>56.98%</td>
											<td>45.53%</td>
										</tr>
										<tr>
											<td>Opera</td>
											<td>Windows</td>
											<td>328</td>
											<td>67.12%</td>
											<td>78.34%</td>
										</tr>
										<tr>
											<td>Opera</td>
											<td>Macintosh</td>
											<td>22</td>
											<td>87.21%</td>
											<td>79.81%</td>
										</tr>
										<tr>
											<td>Chrome</td>
											<td>iOS</td>
											<td>45</td>
											<td>23.21%</td>
											<td>34.67%</td>
										</tr>
										<tr>
											<td>Firefox</td>
											<td>Windows</td>
											<td>67</td>
											<td>27.11%</td>
											<td>78.08%</td>
										</tr>
										<tr>
											<td>Chrome</td>
											<td>Macintosh</td>
											<td>120</td>
											<td>80.78%</td>
											<td>80.77%</td>
										</tr>
										<tr>
											<td>Chrome</td>
											<td>Windows</td>
											<td>682</td>
											<td>89.44%</td>
											<td>95.32%</td>
										</tr>
										<tr>
											<td>Chrome</td>
											<td>Windows</td>
											<td>172</td>
											<td>43.11%</td>
											<td>46.89%</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
						<!-- END FEATURED DATATABLE -->
						<!-- DATA EXPORT FEATURE -->
						<div class="panel">
							<div class="panel-heading">
								<h3 class="panel-title">Datatable with Export Feature</h3>
							</div>
							<div class="panel-body">
								<p class="alert alert-warning"><i class="icon ion-alert-circled"></i> Requires Flash plugin</p>
								<table id="datatable-data-export" class="table table-striped table-hover table-bordered">
									<thead>
										<tr>
											<th>Browser</th>
											<th>Operating System</th>
											<th>Visits</th>
											<th>New Visits</th>
											<th>Bounce Rate</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>Chrome</td>
											<td>Macintosh</td>
											<td>360</td>
											<td>82.78%</td>
											<td>87.77%</td>
										</tr>
										<tr>
											<td>Chrome</td>
											<td>Windows</td>
											<td>582</td>
											<td>87.24%</td>
											<td>90.12%</td>
										</tr>
										<tr>
											<td>Chrome</td>
											<td>Linux</td>
											<td>172</td>
											<td>45.21%</td>
											<td>48.81%</td>
										</tr>
										<tr>
											<td>Chrome</td>
											<td>iOS</td>
											<td>86</td>
											<td>35.23%</td>
											<td>44.21%</td>
										</tr>
										<tr>
											<td>Firefox</td>
											<td>Windows</td>
											<td>280</td>
											<td>63.12%</td>
											<td>89.34%</td>
										</tr>
										<tr>
											<td>Firefox</td>
											<td>Android</td>
											<td>236</td>
											<td>58.02%</td>
											<td>76.19%</td>
										</tr>
										<tr>
											<td>Internet Explorer</td>
											<td>Windows</td>
											<td>145</td>
											<td>45.23%</td>
											<td>94.65%</td>
										</tr>
										<tr>
											<td>Opera</td>
											<td>Windows</td>
											<td>328</td>
											<td>67.12%</td>
											<td>78.34%</td>
										</tr>
										<tr>
											<td>Opera</td>
											<td>Macintosh</td>
											<td>22</td>
											<td>87.21%</td>
											<td>79.81%</td>
										</tr>
										<tr>
											<td>Chrome</td>
											<td>iOS</td>
											<td>45</td>
											<td>23.21%</td>
											<td>34.67%</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
						<!-- END DATA EXPORT FEATURE -->
						<!-- DATATABLE WITH SCROLLING -->
						<div class="panel">
							<div class="panel-heading">
								<h3 class="panel-title">Datatable with Scrolling</h3>
							</div>
							<div class="panel-body">
								<table id="datatable-basic-scrolling" class="table table-sorting table-hover datatable">
									<thead>
										<tr>
											<th>Browser</th>
											<th>Operating System</th>
											<th>Visits</th>
											<th>New Visits</th>
											<th>Bounce Rate</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>Chrome</td>
											<td>Macintosh</td>
											<td>360</td>
											<td>82.78%</td>
											<td>87.77%</td>
										</tr>
										<tr>
											<td>Firefox</td>
											<td>Windows</td>
											<td>280</td>
											<td>63.12%</td>
											<td>89.34%</td>
										</tr>
										<tr>
											<td>Firefox</td>
											<td>Android</td>
											<td>236</td>
											<td>58.02%</td>
											<td>76.19%</td>
										</tr>
										<tr>
											<td>Internet Explorer</td>
											<td>Windows</td>
											<td>145</td>
											<td>45.23%</td>
											<td>94.65%</td>
										</tr>
										<tr>
											<td>Safari</td>
											<td>Macintosh</td>
											<td>103</td>
											<td>22.12%</td>
											<td>52.43%</td>
										</tr>
										<tr>
											<td>Safari</td>
											<td>iOS</td>
											<td>302</td>
											<td>56.98%</td>
											<td>45.53%</td>
										</tr>
										<tr>
											<td>Opera</td>
											<td>Windows</td>
											<td>328</td>
											<td>67.12%</td>
											<td>78.34%</td>
										</tr>
										<tr>
											<td>Opera</td>
											<td>Macintosh</td>
											<td>22</td>
											<td>87.21%</td>
											<td>79.81%</td>
										</tr>
										<tr>
											<td>Chrome</td>
											<td>iOS</td>
											<td>45</td>
											<td>23.21%</td>
											<td>34.67%</td>
										</tr>
										<tr>
											<td>Firefox</td>
											<td>Windows</td>
											<td>67</td>
											<td>27.11%</td>
											<td>78.08%</td>
										</tr>
										<tr>
											<td>Chrome</td>
											<td>Macintosh</td>
											<td>120</td>
											<td>80.78%</td>
											<td>80.77%</td>
										</tr>
										<tr>
											<td>Chrome</td>
											<td>Windows</td>
											<td>682</td>
											<td>89.44%</td>
											<td>95.32%</td>
										</tr>
										<tr>
											<td>Chrome</td>
											<td>Windows</td>
											<td>172</td>
											<td>43.11%</td>
											<td>46.89%</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
						<!-- END DATATABLE WITH SCROLLING -->
					</div>
				</div>
				<!-- END MAIN CONTENT -->
				<!-- RIGHT SIDEBAR -->
				<div id="sidebar-right" class="right-sidebar">
					<div class="sidebar-widget">
						<h4 class="widget-heading"><i class="fa fa-calendar"></i> TODAY</h4>
						<p class="date">Wednesday, 22 December</p>
						<div class="row margin-top-30">
							<div class="col-xs-4">
								<a href="#">
									<div class="icon-transparent-area custom-color-blue first">
										<i class="fa fa-tasks"></i>
										<span>Tasks</span>
										<span class="badge">5</span>
									</div>
								</a>
							</div>
							<div class="col-xs-4">
								<a href="#">
									<div class="icon-transparent-area custom-color-green">
										<i class="fa fa-envelope"></i>
										<span>Mail</span>
										<span class="badge">12</span>
									</div>
								</a>
							</div>
							<div class="col-xs-4">
								<a href="#">
									<div class="icon-transparent-area custom-color-orange last">
										<i class="fa fa-user-plus"></i>
										<span>Users</span>
										<span class="badge">24</span>
									</div>
								</a>
							</div>
						</div>
					</div>
					<div class="sidebar-widget">
						<div class="widget-header">
							<h4 class="widget-heading">YOUR APPS</h4>
							<a href="#" class="show-all">Show all</a>
						</div>
						<div class="row">
							<div class="col-xs-3">
								<a href="#" class="icon-app" title="Dropbox" data-toggle="tooltip" data-placement="top">
									<i class="fa fa-dropbox dropbox-color"></i>
								</a>
							</div>
							<div class="col-xs-3">
								<a href="#" class="icon-app" title="WordPress" data-toggle="tooltip" data-placement="top">
									<i class="fa fa-wordpress wordpress-color"></i>
								</a>
							</div>
							<div class="col-xs-3">
								<a href="#" class="icon-app" title="Drupal" data-toggle="tooltip" data-placement="top">
									<i class="fa fa-drupal drupal-color"></i>
								</a>
							</div>
							<div class="col-xs-3">
								<a href="#" class="icon-app" title="Github" data-toggle="tooltip" data-placement="top">
									<i class="fa fa-github github-color"></i>
								</a>
							</div>
						</div>
					</div>
					<div class="sidebar-widget">
						<div class="widget-header">
							<h4 class="widget-heading">MY PROJECTS</h4>
							<a href="#" class="show-all">Show all</a>
						</div>
						<ul class="list-unstyled list-project-progress">
							<li>
								<a href="#" class="project-name">Project XY</a>
								<div class="progress progress-xs progress-transparent custom-color-orange">
									<div class="progress-bar" role="progressbar" aria-valuenow="67" aria-valuemin="0" aria-valuemax="100" style="width:67%"></div>
								</div>
								<span class="percentage">67%</span>
							</li>
							<li>
								<a href="#" class="project-name">Growth Campaign</a>
								<div class="progress progress-xs progress-transparent custom-color-blue">
									<div class="progress-bar" role="progressbar" aria-valuenow="23" aria-valuemin="0" aria-valuemax="100" style="width:23%"></div>
								</div>
								<span class="percentage">23%</span>
							</li>
							<li>
								<a href="#" class="project-name">Website Redesign</a>
								<div class="progress progress-xs progress-transparent custom-color-green">
									<div class="progress-bar" role="progressbar" aria-valuenow="87" aria-valuemin="0" aria-valuemax="100" style="width:87%"></div>
								</div>
								<span class="percentage">87%</span>
							</li>
						</ul>
					</div>
					<div class="sidebar-widget">
						<div class="widget-header">
							<h4 class="widget-heading">MY FILES</h4>
							<a href="#" class="show-all">Show all</a>
						</div>
						<ul class="list-unstyled list-justify list-file-simple">
							<li><a href="#"><i class="fa fa-file-word-o"></i>Proposal_draft.docx</a>
								<span>4 MB</span>
							</li>
							<li><a href="#"><i class="fa fa-file-pdf-o"></i>Manual_Guide.pdf</a>
								<span>20 MB</span>
							</li>
							<li><a href="#"><i class="fa fa-file-zip-o"></i>all-project-files.zip</a>
								<span>315 MB</span>
							</li>
							<li><a href="#"><i class="fa fa-file-excel-o"></i>budget_estimate.xls</a>
								<span>1 MB</span>
							</li>
						</ul>
					</div>
					<p class="text-center"><a href="#" class="btn btn-default btn-xs">More Widgets</a></p>
				</div>
				<!-- END RIGHT SIDEBAR -->
			</div>
			<!-- END MAIN -->
			<div class="clearfix"></div>
			<footer>
				<div class="container-fluid">
					<p class="copyright">&copy; 2017 <a href="https://www.themeineed.com" target="_blank">Theme I Need</a>. All Rights Reserved.</p>
				</div>
			</footer>
		</div>
		<!-- END WRAPPER -->
		<!-- Javascript -->
		<script src="assets/vendor/jquery/jquery.min.js"></script>
		<script src="assets/vendor/bootstrap/js/bootstrap.min.js"></script>
		<script src="assets/vendor/pace/pace.min.js"></script>
		<script src="assets/vendor/datatables/js-main/jquery.dataTables.min.js"></script>
		<script src="assets/vendor/datatables/js-bootstrap/dataTables.bootstrap.min.js"></script>
		<script src="assets/vendor/datatables-colreorder/dataTables.colReorder.js"></script>
		<script src="assets/vendor/datatables-tabletools/js/dataTables.tableTools.js"></script>
		<script src="assets/scripts/klorofilpro-common.min.js"></script>
		<!-- DEMO PANEL -->
		<!-- for demo purpose only, you should remove it on your project directory -->
		<script type="text/javascript">
		var toggleDemoPanel = function(e)
		{
			e.preventDefault();
			var panel = document.getElementById('demo-panel');
			if (panel.className) panel.className = '';
			else panel.className = 'active';
		}
		// fix each iframe src when back button is clicked
		$(function()
		{
			$('iframe').each(function()
			{
				this.src = this.src;
			});
		});
		</script>
		<div id="demo-panel">
			<a href="#" onclick="toggleDemoPanel(event);"><i class="fa fa-cog fa-spin"></i></a>
			<iframe src="demo-panel/index.html"></iframe>
		</div>
		<!-- END DEMO PANEL -->
		<script>
		$(function()
		{
			// datatable column with reorder extension
			$('#datatable-column-reorder').dataTable(
			{
				pagingType: "full_numbers",
				sDom: "RC" +
					"t" +
					"<'row'<'col-sm-6'i><'col-sm-6'p>>",
				colReorder: true,
			});
			// datatable with column filter enabled
			var dtTable = $('#datatable-column-filter').DataTable(
			{ // use DataTable, not dataTable
				sDom: // redefine sDom without lengthChange and default search box
					"t" +
					"<'row'<'col-sm-6'i><'col-sm-6'p>>"
			});
			$('#datatable-column-filter thead').append('<tr class="row-filter"><th></th><th></th><th></th><th></th><th></th></tr>');
			$('#datatable-column-filter thead .row-filter th').each(function()
			{
				$(this).html('<input type="text" class="form-control input-sm" placeholder="Search...">');
			});
			$('#datatable-column-filter .row-filter input').on('keyup change', function()
			{
				dtTable
					.column($(this).parent().index() + ':visible')
					.search(this.value)
					.draw();
			});
			// datatable with paging options and live search
			$('#featured-datatable').dataTable(
			{
				sDom: "<'row'<'col-sm-6'l><'col-sm-6'f>r>t<'row'<'col-sm-6'i><'col-sm-6'p>>",
			});
			// datatable with export feature
			var exportTable = $('#datatable-data-export').DataTable(
			{
				sDom: "T<'clearfix'>" +
					"<'row'<'col-sm-6'l><'col-sm-6'f>r>" +
					"t" +
					"<'row'<'col-sm-6'i><'col-sm-6'p>>",
				"tableTools":
				{
					"sSwfPath": "assets/vendor/datatables-tabletools/swf/copy_csv_xls_pdf.swf"
				}
			});
			// datatable with scrolling
			$('#datatable-basic-scrolling').dataTable(
			{
				scrollY: "300px",
				scrollCollapse: true,
				paging: false
			});
		});
		</script>
	</body>
</html>