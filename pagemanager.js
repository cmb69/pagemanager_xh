/**
 * Page administration of Pagemanager_XH
 *
 * Copyright (c) 2011-2013 Christoph M. Becker (see license.txt)
 */


// utf-8 marker: äöüß


function pagemanager_do(op) {
    switch (op) {
	case 'expand': realOp = 'open_all'; break;
	case 'collapse': realOp = 'close_all'; break;
	case 'create_after': realOp = 'create'; break;
	case 'delete': realOp = 'remove'; break;
	case 'paste_after': realOp = 'pasteAfter'; break;
	default: realOp = op;
    }
    (function ($) {
	switch (op) {
	    case 'expand':
	    case 'collapse':
		$('#pagemanager').jstree(realOp);
		break;
	    case 'save':
		$('#pagemanager-xml')[0].value = $('#pagemanager').jstree(
			'get_xml', 'nest', -1, new Array('id', 'title', 'pdattr'));
		$('#pagemanager-form').submit();
		break;
	    default:
		sel = $('#pagemanager').jstree('get_selected');
		if (sel.length > 0) {
		    if (op == 'create_after') {
			$('#pagemanager').jstree(realOp, sel, 'after');
		    } else {
			$('#pagemanager').jstree(realOp, sel);
		    }
		} else {
		    if ((PAGEMANAGER["verbose"]).toLowerCase() == 'true') {
			$('#pagemanager-alert').html(PAGEMANAGER["message_no_selection"]);
			$('#pagemanager-alert').dialog('open');
		    }
		}
	}
    })(jQuery);
}


function pagemanager_confirmStructureWarning() {
    jQuery('#pagemanager-structure-warning').hide(500);
    jQuery('#pagemanager-toolbar a:first-child').show();
    jQuery('#pagemanager-submit').show();
}


function pagemanager_level(obj) {
    var res = 0;
    while (obj.attr('id') != 'pagemanager') {
	obj = obj.parent().parent();
	res++;
    }
    return res;
}


function pagemanager_childLevels(obj) {
    var res = -1;
    while (obj.length > 0) {
	obj = obj.find('li');
	res++;
    }
    return res;
}


function pagemanager_markDuplicates(node, duplicates) {
    var pagemanager = jQuery.jstree._reference('#pagemanager');
    var children = pagemanager._get_children(node);
    for (var i = 0; i < children.length; i++) {
	duplicates = pagemanager_markDuplicates(children[i], duplicates);
	for (var j = i+1; j < children.length; j++) {
	    if (pagemanager.get_text(children[i]) == pagemanager.get_text(children[j])) {
		pagemanager.set_text(children[j], PAGEMANAGER["toc_dupl"] + ++duplicates);
	    }
	}
    }
    return duplicates;
};


var pagemanager_modified = false;


(function ($) {
    $(function () {
	if (typeof $.jstree == 'undefined') {
	    alert(PAGEMANAGER["error_offending_extension"]);
	    return;
	}
	$.jstree.plugin('crrm', {
	    _fn: {
		pasteAfter: function(obj) {
		    obj = this._get_node(obj);
		    if(!obj || !obj.length) { return false; }
		    var nodes = this.data.crrm.ct_nodes ? this.data.crrm.ct_nodes : this.data.crrm.cp_nodes;
		    if(!this.data.crrm.ct_nodes && !this.data.crrm.cp_nodes) { return false; }
		    if(this.data.crrm.ct_nodes) { this.move_node(this.data.crrm.ct_nodes, obj, 'after'); this.data.crrm.ct_nodes = false; }
		    if(this.data.crrm.cp_nodes) { this.move_node(this.data.crrm.cp_nodes, obj, 'after', true); }
		    this.__callback({ "obj" : obj, "nodes" : nodes });
		    return undefined; // to satisfy lint
		}
	    }
	});

	$('#pagemanager-confirmation').dialog({
	    'autoOpen': false,
	    'modal': true
	});

	var pagemanagerButtons = {};
	pagemanagerButtons[PAGEMANAGER["button_ok"]] = function () {$(this).dialog('close');}
	$('#pagemanager-alert').dialog({
	    'autoOpen': false,
	    'modal': true,
	    'buttons': pagemanagerButtons
	});

	$('#pagemanager').bind('loaded.jstree', function () {
	    if ($('#pagemanager-structure-warning').length == 0) {
	        $('#pagemanager-toolbar a:first-child').show();
		$('#pagemanager-submit').show();
	    }
	});

	/* initialize checkboxes */
	$('#pagemanager').bind('loaded.jstree', function () {
	    var checkNodes = (function (parent) {
		var nodes = pagemanager._get_children(parent);
		for (var i = 0; i < nodes.length; ++i) {
		    var node = pagemanager._get_node(nodes[i]);
		    if (node.attr('pdattr') == '1') {
			pagemanager.check_node(node);
		    }
		    checkNodes(node);
		}
	    });
	    checkNodes(-1);
	    $('#pagemanager').bind('move_node.jstree create_node.jstree rename_node.jstree remove.jstree change_state.jstree', function () {
		pagemanager_modified = true;
	    });
	});

	$('#pagemanager').bind('before.jstree', function (e, data) {
	    switch (data.func) {
		case 'create_node':
		    if (pagemanager_level(pagemanager._get_node(data.args[0]))
			    >= PAGEMANAGER["menu_levels"] + (data.args[1] == 'after' ? 1 : 0)) {
			if ((PAGEMANAGER["verbose"]).toLowerCase() == 'true') {
			    $('#pagemanager-alert').html(PAGEMANAGER["message_menu_level"]);
			    $('#pagemanager-alert').dialog('open');
			}
			e.stopImmediatePropagation();
			return false;
		    }
		    break;
		case 'rename':
		    if ($(data.args[0][0]).hasClass('pagemanager-no-rename')) {
			alert(PAGEMANAGER["error_cant_rename"]);
			e.stopImmediatePropagation();
			return false;
		    }
		    pagemanager.set_text(data.args[0][0], pagemanager._get_node(data.args[0][0]).attr('title'));
		    break;
		case 'remove':
		    var toplevels = pagemanager._get_children(-1);
		    if (toplevels.length == 1 && data.args[0][0] == toplevels[0]) {
			if ((PAGEMANAGER["verbose"]).toLowerCase() == 'true') {
			    $('#pagemanager-alert').html(PAGEMANAGER["message_delete_last"]);
			    $('#pagemanager-alert').dialog('open');
			}
			e.stopImmediatePropagation();
			return false;
		    }
		    if (data.args[1] != 'confirmed') {
			if ((PAGEMANAGER["verbose"]).toLowerCase() == 'true') {
			    $('#pagemanager-confirmation').html(PAGEMANAGER["message_confirm_deletion"]);
			    var pagemanagerButtons = {};
			    pagemanagerButtons[PAGEMANAGER["button_delete"]] = function () {
				    pagemanager.remove(data.args[0], 'confirmed');
				    $(this).dialog('close');
				    }
			    pagemanagerButtons[PAGEMANAGER["button_cancel"]] = function () {
				    $(this).dialog('close');
				}
			    $('#pagemanager-confirmation').dialog('option', 'buttons', pagemanagerButtons);
			    $('#pagemanager-confirmation').dialog('open');
			    e.stopImmediatePropagation();
			    return false;
			}
		    }
		    break;
		default:
	    }
	    return undefined; // to satisfy lint
	});

	$('#pagemanager').bind('change_state.jstree', function (e, data) {
	    data.rslt.attr('pdattr', data.args[1] ? '0' : '1');
	});

	$('#pagemanager').bind('create_node.jstree', function (e, data) {
	    pagemanager.set_type('new', data.rslt.obj);
	    pagemanager.check_node(data.rslt.obj);
	});

	$('#pagemanager').bind('rename_node.jstree', function (e, data) {
	    pagemanager._get_node(data.rslt.obj).attr('title', pagemanager.get_text(data.rslt.obj));
	});

	/* mark copied nodes as new */
	$('#pagemanager').bind('move_node.jstree', function (e, data) {
	    if ('cy' in data.rslt && data.rslt.cy) {
		var traverse = (function (node) {
		    var children = pagemanager._get_children(node);
		    for (var i = 0; i < children.length; ++i) {
			pagemanager.set_type('new', children[i]);
			traverse(children[i]);
		    }
		});
		pagemanager.set_type('new', data.rslt.oc);
		traverse(data.rslt.oc);
	    }
	});

	/* restore page titles */
	$('#pagemanager').bind('rename_node.jstree remove.jstree move_node.jstree', function (e, data) {
	    var restoreTitles = (function (node) {
		var children = pagemanager._get_children(node);
		for (var i = 0; i < children.length; ++i) {
		    pagemanager.set_text(children[i], pagemanager._get_node(children[i]).attr('title'));
		    restoreTitles(children[i]);
		}
	    });
	    restoreTitles(-1);
	});

	/* mark duplicate headers */
	$('#pagemanager').bind('loaded.jstree rename_node.jstree remove.jstree move_node.jstree', function (e, data) {
	    pagemanager_markDuplicates(-1, 0);
	});

	if (!window.opera) {
	    window.onbeforeunload = function () {
		if (pagemanager_modified && $('#pagemanager-xml')[0].value == '') {
		    return PAGEMANAGER["message_warning_leave"];
		}
		return undefined; // to satisfy lint
	    };
	} else {
	    $(window).unload(function () {
		if (pagemanager_modified && $('#pagemanager-xml')[0].value == '') {
		    if (confirm(PAGEMANAGER["message_confirm_leave"])) {
			$('#pagemanager-xml')[0].value = pagemanager.get_xml(
				'nest', -1, new Array('id', 'title', 'pdattr'));
			$('#pagemanager-form').submit();
		    }
		}
	    });
	}

	/*
	 * Initialize jsTree.
	 */

	$('#pagemanager').jstree({
	    'plugins': ['themes', 'html_data', 'xml_data', 'dnd', 'ui',
		    'crrm', 'contextmenu', 'checkbox', 'types'],
	    'core': {
		'animation': PAGEMANAGER["treeview_animation"],
		'strings': {
		    loading: PAGEMANAGER["treeview_loading"],
		    new_node: PAGEMANAGER["treeview_new"]
		}
	    },
	    'types': {
		'types': {
		    'new': {
			'icon': {
			    'image': PAGEMANAGER["image_dir"] + "new.gif"
			}
		    },
		    'default': {
		    }
		}
	    },
	    'checkbox': {
		'checked_parent_open': false,
		'two_state': true
	    },
	    'ui': {
		'select_limit': 1
	    },
	    'crrm': {
		'move': {
		    'check_move': function (m) {
			    var sc = pagemanager_childLevels(m.o),
				tl = pagemanager_level(m.r);
			    var allowed =
				    (typeof(m.r) == 'object' && pagemanager_childLevels(m.o) // of source
					+ pagemanager_level(m.r) // of target
					+ (m.p == 'last' || m.p == 'inside' ? 1 : 0) // paste vs. dnd
				    <= PAGEMANAGER["menu_levels"]);
			    if (!m.ot.data.dnd.active && !allowed
				    && PAGEMANAGER["verbose"].toLowerCase() == 'true') {
				$('#pagemanager-alert').html(PAGEMANAGER["message_menu_level"]);
				$('#pagemanager-alert').dialog('open');
			    }
			    return allowed;
		    }
		}
	    },
	    'themes': {
		'theme': PAGEMANAGER["treeview_theme"]
	    },
	    'contextmenu': {
		'show_at_node': false,
		'select_node': true,
		'items': function (node) {
		    return {
			'create': {
			    'label': PAGEMANAGER["op_create"],
			    'icon': PAGEMANAGER["image_dir"] + "create" + PAGEMANAGER["image_ext"],
			    'action': function (obj) {this.create(obj);}
			},
			'create-after': {
			    'label': PAGEMANAGER["op_create_after"],
			    'icon': PAGEMANAGER["image_dir"] + "create_after" + PAGEMANAGER["image_ext"],
			    'action': function(obj) {this.create(obj, 'after');}
			},
			'rename': {
			    'label': PAGEMANAGER["op_rename"],
			    'icon': PAGEMANAGER["image_dir"] + "rename" + PAGEMANAGER["image_ext"],
			    'action': function(obj) {this.rename(obj);}
			},
			'remove' : {
			    'label': PAGEMANAGER["op_delete"],
			    'icon': PAGEMANAGER["image_dir"] + "delete" + PAGEMANAGER["image_ext"],
			    'action': function(obj) {this.remove(obj);}
			},
			'cut': {
			    'label': PAGEMANAGER["op_cut"],
			    'separator_before': true,
			    'icon': PAGEMANAGER["image_dir"] + "cut" + PAGEMANAGER["image_ext"],
			    'action': function(obj) {this.cut(obj);}
			},
			'copy': {
			    'label': PAGEMANAGER["op_copy"],
			    'icon': PAGEMANAGER["image_dir"] + "copy" + PAGEMANAGER["image_ext"],
			    'action': function(obj) {this.copy(obj);}
			},
			'paste': {
			    'label': PAGEMANAGER["op_paste"],
			    'icon': PAGEMANAGER["image_dir"] + "paste" + PAGEMANAGER["image_ext"],
			    'action': function(obj) {this.paste(obj);}
			},
			'paste-after': {
			    'label': PAGEMANAGER["op_paste_after"],
			    'icon': PAGEMANAGER["image_dir"] + "paste_after" + PAGEMANAGER["image_ext"],
			    'action': function(obj) {this.pasteAfter(obj);}
			}
		    }
		}
	    }
	});
	var pagemanager = $.jstree._reference('#pagemanager');
    });
})(jQuery);
