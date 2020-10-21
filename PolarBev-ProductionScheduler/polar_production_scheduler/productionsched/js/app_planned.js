var dragPlanWidth = 0;
var boolCloseSession = false;

$('document').ready(function(){

	var plannedOrdersURL = 'plannedOrdersRtv.php?' + planned_orders_query_str;

	// Configure the jqGrid to retrieve data from plannedOrdersRtv.php
	$("#plannedList").jqGrid({
	    url: plannedOrdersURL,
	    datatype: 'json',
	    mtype: 'GET',
	    colNames:['IT','GTech', 'Routing','Pack','Type',
	              'Item#', 'Description', 'Due Date', 'Plan',
	              'Hrs', 'OH', 'Avail', 'Reschedule'],
	    colModel :[
	      {name:'ITEM_TYPE', index:'ITEM_TYPE', width:26,
				searchoptions:{
					sopt:['eq','ne','lt','le','gt','ge','bw','bn','in','ni','ew','en','cn','nc'] }
				},
	      {name:'GROUP_TECH', index:'GROUP_TECH', width:60,
	    	  	searchoptions:{
	    	  		sopt:['eq','ne','lt','le','gt','ge','bw','bn','in','ni','ew','en','cn','nc'] }
	      		},
	      {name:'ROUTING_METHOD', index:'ROUTING_METHOD', width:60,
	    	  	searchoptions:{
	    	  		sopt:['eq','ne','lt','le','gt','ge','bw','bn','in','ni','ew','en','cn','nc'] }
	      		},
	      {name:'PACKAGE', index:'PACKAGE', width:60,
	    	  	searchoptions:{
	    	  		sopt:['eq','ne','lt','le','gt','ge','bw','bn','in','ni','ew','en','cn','nc'] }
	      		},
	      {name:'ORDER_TYPE', index:'ORDER_TYPE', width:50,
	    	  	searchoptions:{
	    	  		sopt:['eq','ne','lt','le','gt','ge','bw','bn','in','ni','ew','en','cn','nc'] }
	      		},
	      {name:'ITEM_NUMBER', index:'ITEM_NUMBER', width:70,
	    	  	searchoptions:{
	    	  		sopt:['eq','ne','lt','le','gt','ge','bw','bn','in','ni','ew','en','cn','nc'] }
	      		},
	      {name:'ITEM_DESC', index:'ITEM_DESC', width:210,
	    	  	searchoptions:{
	    	  		sopt:['eq','ne','lt','le','gt','ge','bw','bn','in','ni','ew','en','cn','nc'] }
	      		},
	      {name:'DUE_DATE', index:'DUE_DATE', width:95, align:'right',
		      	searchoptions:{
		      		dataInit:function(el){$(el).datepicker({dateFormat:'yymmdd'});},
		      		searchrules:{integer:true},
		      		sopt:['eq','ne','lt','le','gt','ge'] }
	      		},
	      {name:'PLAN_QTY', index:'PLAN_QTY', width:55, align:'right',
	      		searchoptions:{
	      			searchrules:{integer:true},
	      			sopt:['eq','ne','lt','le','gt','ge'] }
	      		},
	      {name:'HOURS', index:'HOURS', width:45, align:'right',
	      		searchoptions:{
	      			searchrules:{number:true},
	      			sopt:['eq','ne','lt','le','gt','ge'] }
	      		},
	      {name:'ON_HAND', index:'ON_HAND', width:55, align:'right',
	      		searchoptions:{
	      			searchrules:{integer:true},
	      			sopt:['eq','ne','lt','le','gt','ge'] }
	      		},
	      {name:'AVAIL', index:'AVAIL', width:55, align:'right',
	      		searchoptions:{
	      			searchrules:{integer:true},
	      			sopt:['eq','ne','lt','le','gt','ge'] }
	      		},
	      {name:'RESCHEDULE', index:'RESCHEDULE', width:85, align:'right',
		      	searchoptions:{
		      		dataInit:function(el){$(el).datepicker({dateFormat:'yymmdd'});},
		      		searchrules:{integer:true},
		      		sopt:['eq','ne','lt','le','gt','ge'] }
	      		}
	    ],
	    pager: '#plannedPager',
	    rowNum: 200,
	    rowList:[250,500,1000],
	    rownumbers: true,
	    sortname: 'DUE_DATE',
	    sortorder: 'asc',
	    viewrecords: true,
	    gridview: true,
	    caption: planned_orders_caption,
	    autowidth : true,
//   	height: 'auto'
	});

/*	jQuery("#plannedList tr").draggable({
		snap: ".dayColumn",
		snapTolerance: 10,
		helper: 'clone',
		opacity: 0.35
	});
*/
	jQuery("#plannedList").jqGrid('navGrid','#plannedPager', {
		edit:false,
		add:false,
		del:false,
		search:true,
		refresh:true },
		{}, // edit options
		{}, // add options
		{}, //del options
		{multipleSearch:true} // search options
	);

	//
	jQuery("#plannedList").jqGrid('gridDnD',{
		connectWith:'#dailylist',
		cursor: "move",
		cursorAt: 'center',
		scroll: false ,
		drag_opts:{
			helper: function( event ) {
				var draggedID = '#' + $(this).attr('id');
				var itemNo = $(draggedID + ' td[aria-describedby="plannedList_ITEM_NUMBER"]').html();
				var itemDesc = $(draggedID + ' td[aria-describedby="plannedList_ITEM_DESC"]').html();
				dragPlanWidth = itemDesc.length;
				var quantity = $(draggedID + ' td[aria-describedby="plannedList_PLAN_QTY"]').html();
				var dragText = 'Item#: ' + itemNo + '<br>' + itemDesc + '<br>' + 'Quantity = ' + quantity;
				helperHTML = '<div id="dragPlan" class="dragPlanned">' + dragText + '</div>';
				return $(helperHTML);
			},
	 		appendTo : 'body',
	 		cursorAt: { left: 85, top: 5 }
		}
  });

  // make planned orders list resizable
  $( ".ui-jqgrid-bdiv" ).resizable({handles: 's'});
  $( ".ui-jqgrid-bdiv" ).css('border-bottom', '2px solid #70a8d2');

  // make each panel draggable and resizable
  $( "div#top_panels" ).draggable({stack: '#weekly-container, #daily'});
//  $( "div#top_panels" ).resizable({handles: 'n, s, e, w, nw,ne,sw,se'});

  $( "div#daily" ).draggable({stack: '#weekly-container, #top_panels'});
//  $( "div#daily" ).resizable({handles: 'n, s, e, w, nw,ne,sw,se'});

  $( "div#weekly-container" ).draggable({stack: '#top_panels, #daily'});

//  <div class="ui-jqgrid-titlebar ui-widget-header ui-corner-top ui-helper-clearfix"><a style="right: 0px;" class="ui-jqgrid-titlebar-close HeaderButton" role="link" href="javascript:void(0)"><span class="ui-icon ui-icon-circle-triangle-n"></span></a><span class="ui-jqgrid-title">Planned Orders - from Mon 02/29/2016 through Fri 03/11/2016</span></div>

});

