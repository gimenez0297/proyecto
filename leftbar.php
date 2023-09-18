
  <aside class="left-sidebar">
	<div class="d-flex no-block nav-text-box align-items-center">
		<span id="open_img" class="activo"><img src="<?php echo $logo_horizontal; ?>" alt="<?php echo $establecimiento; ?>"></span>
		<span id="close_img"><img src="<?php echo $logo_close; ?>" alt="<?php echo $establecimiento; ?>"></span>
<!--		<a class="nav-lock waves-effect waves-dark ml-auto hidden-md-down" href="javascript:void(0)"><i class="mdi mdi-toggle-switch"></i></a>-->
		<!-- <a class="nav-toggler waves-effect waves-dark ml-auto hidden-sm-up" href="javascript:void(0)"><i class="ti-menu ti-close"></i></a> -->
	</div>
	<!-- Sidebar scroll-->
	<div class="scroll-sidebar">
		<!-- Sidebar navigation-->
		<nav class="sidebar-nav">
			<ul id="sidebarnav">
				<?php menu($auth->getUserId()); ?>
			</ul>
		</nav>
		<!-- End Sidebar navigation -->
	</div>
	<!-- End Sidebar scroll-->
    </aside>
