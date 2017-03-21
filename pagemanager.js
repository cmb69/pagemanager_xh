/**
 * Copyright 2011-2017 Christoph M. Becker
 *
 * This file is part of Pagemanager_XH.
 *
 * Pagemanager_XH is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Pagemanager_XH is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Pagemanager_XH.  If not, see <http://www.gnu.org/licenses/>.
 */

/*jslint browser: true, maxlen: 80, nomen: true*/
/*global jQuery, PAGEMANAGER*/

(function ($) {
    "use strict";

    var element = null,
        widget = null,
        modified = false,
        init;

    /**
     * Returns the level of the page.
     *
     * @param {Elements} obj
     *
     * @returns {Number}
     */
    function level(obj) {
        var res = 0;

        while (obj.attr("id") !== "pagemanager") {
            obj = obj.parent().parent();
            res += 1;
        }
        return res;
    }

    /**
     * Returns the nesting level of the children.
     *
     * @param {Elements} obj
     *
     * @returns {Number}
     */
    function childLevels(obj) {
        var res = -1;

        while (obj.length > 0) {
            obj = obj.find("li");
            res += 1;
        }
        return res;
    }

    /**
     * Checks resp. unchecks all child pages of a page.
     *
     * @param {Element} parent
     *
     * @return {undefined}
     */
    function checkPages(parent) {
        var nodes, i, node;

        nodes = widget.get_children_dom(parent);
        for (i = 0; i < nodes.length; i += 1) {
            node = widget.get_node(nodes[i], true);
            if (node.attr("data-pdattr") === "1") {
                widget.check_node(node);
            }
            checkPages(node);
        }
    }

    /**
     * Marks new pages as such.
     *
     * @param {Element} node
     *
     * @returns {undefined}
     */
    function markNewPages(node) {
        $.each(node.children, function (index, value) {
            widget.set_type(value, "new");
            markNewPages(widget.get_node(value));
        });
    }

    /**
     * Marks copied pages as new.
     *
     * @param {Object} event
     * @param {Object} data
     *
     * @returns {undefined}
     */
    function markCopiedPages(event, data) {
        widget.set_type(data.node, "new");
        markNewPages(data.node);
    }

    /**
     * Prepares the form submission.
     *
     * @returns {undefined}
     */
    function beforeSubmit() {
        var attribs, json;

        attribs = ["id", "title", "data-pdattr", "class"];
        json = JSON.stringify(widget.get_json(-1, attribs));
        $("#pagemanager_json").val(json);
    }

    /**
     * Submits the page structure.
     *
     * @returns {undefined}
     */
    function submit() {
        var url, form, data, message, status, request;

        function onReadyStateChange() {
            if (request.readyState === 4) {
                status.css("display", "none");
                if (request.status === 200) {
                    message = request.responseText;
                } else {
                    message = "<p class=\"xh_fail\"><strong>" + request.status +
                            " " + request.statusText + "</strong><br>" +
                            request.responseText + "</p>";
                }
                status.after(message);
                // TODO: optimization: fix structure instead of reloading
                widget.destroy();
                init();
            }
        }

        beforeSubmit();
        form = $("#pagemanager_form");
        url = form.attr("action");
        message = form.children(
            ".xh_success, .xh_fail, .cmsimplecore_success, .cmsimplecore_fail"
        );
        message.remove();
        status = $(".pagemanager_status");
        status.css("display", "block");
        data = form.serialize();
        request = new XMLHttpRequest();
        request.open("POST", url);
        request.setRequestHeader("Content-Type",
                "application/x-www-form-urlencoded");
        request.onreadystatechange = onReadyStateChange;
        request.send(data);
    }

    /**
     * Hides the irregular page structure warning and shows the save buttons.
     *
     * @returns {undefined}
     */
    function confirmStructureWarning() {
        $("#pagemanager_structure_warning").hide(500);
        $("#pagemanager_save, #pagemanager_submit").show();
    }

    /**
     * Displays an alert dialog.
     *
     * @param {String} message
     *
     * @returns {undefined}
     */
    function alert(message) {
        $("#pagemanager_alert").html(message).dialog("open");
    }

    function doCreate(node) {
        var id = widget.create_node(node, PAGEMANAGER.newNode);
        widget.edit(id);
    }

    function doCreateAfter(node) {
        var node = widget.get_node(node);
        var parent = widget.get_node(node.parent);
        var pos = $.inArray(node.id, parent.children);
        var id = widget.create_node(parent, PAGEMANAGER.newNode, pos + 1);
        widget.edit(id);
    }

    function doRename(node) {
        var li = $("#" + widget.get_node(node).li_attr.id);
        widget.set_text(node, li.attr("title"));
        widget.edit(node);
    }

    function doDelete(node) {
        widget.delete_node(node);
    }

    function doCut(node) {
        widget.cut(node);
    }

    function doCopy(node) {
        widget.copy(node);
    }

    function doPaste(node) {
        widget.paste(node, "last");
    }

    function doPasteAfter(node) {
        var node = widget.get_node(node);
        var parent = widget.get_node(node.parent);
        var pos = $.inArray(node.id, parent.children);
        widget.paste(parent, pos + 1);
    }

    /**
     * Do an operation on the currently selected node.
     *
     * @param {String} operation
     *
     * @returns {undefined}
     */
    function doWithSelection(operation) {
        var selection;

        selection = widget.get_selected();
        if (selection.length > 0) {
            switch (operation) {
                case "create":
                    doCreate(selection);
                    break;
                case "create_after":
                    doCreateAfter(selection);
                    break;
                case "rename":
                    doRename(selection);
                    break;                    
                case "delete":
                    doDelete(selection);
                    break;
                case "cut":
                    doCut(selection);
                    break;
                case "copy":
                    doCopy(selection);
                    break;
                case "paste":
                    doPaste(selection);
                    break;
                case "paste_after":
                    doPasteAfter(selection);
                    break;
            }
        } else {
            if (PAGEMANAGER.verbose) {
                alert(PAGEMANAGER.noSelectionMessage);
            }
        }
    }

    /**
     * Execute a tool.
     *
     * @param {String} operation
     *
     * @returns {undefined}
     */
    function tool(operation) {
        switch (operation) {
        case "expand":
            widget.open_all();
            break;
        case "collapse":
            widget.close_all();
            break;
        case "save":
            submit();
            break;
        default:
            doWithSelection(operation);
        }
    }

    /**
     * Prevents creating a page if not allowed.
     *
     * @param {Event}  event
     * @param {Object} data
     *
     * @returns {mixed}
     */
    function beforeCreateNode(event, data) {
        var node, where, targetLevel, result;

        node = data.args[0];
        where = data.args[1];
        targetLevel = level(node) - (where === "after" ? 1 : 0);
        if (targetLevel < PAGEMANAGER.menuLevels) {
            result = undefined;
        } else {
            if (PAGEMANAGER.verbose) {
                alert(PAGEMANAGER.menuLevelMessage);
            }
            event.stopImmediatePropagation();
            result = false;
        }
        return result;
    }

    /**
     * Prepares renaming a node.
     *
     * @param {Event}  event
     * @param {Object} data
     *
     * @returns {mixed}
     */
    function beforeRename(event, data) {
        var node = data.args[0], title, result;

        if (!node.hasClass("pagemanager_no_rename")) {
            title = node.attr("title");
            widget.set_text(node, title);
            result = undefined;
        } else {
            alert(PAGEMANAGER.cantRenameError);
            event.stopImmediatePropagation();
            result = false;
        }
    }

    /**
     * Prepares deleting a page.
     *
     * @param {Event}  event
     * @param {Object} data
     *
     * @returns {mixed}
     */
    function beforeRemove(event, data) {
        var node, what, toplevelNodes, buttons;

        node = data.args[0];
        what = data.args[1];
        toplevelNodes = widget.get_container_ul().children();

        // prevent deletion of last toplevel node
        if (toplevelNodes.length === 1 &&
                node.get(0) === toplevelNodes.get(0)) {
            if (PAGEMANAGER.verbose) {
                alert(PAGEMANAGER.deleteLastMessage);
            }
            event.stopImmediatePropagation();
            return false;
        }

        // confirmation
        if (what !== "confirmed") {
            if (PAGEMANAGER.verbose) {
                buttons = {};
                buttons[PAGEMANAGER.deleteButton] = function () {
                    widget.remove(node, "confirmed");
                    $(this).dialog("close");
                };
                buttons[PAGEMANAGER.cancelButton] = function () {
                    $(this).dialog("close");
                };
                $("#pagemanager_confirmation")
                    .html(PAGEMANAGER.confirmDeletionMessage)
                    .dialog("option", "buttons", buttons)
                    .dialog("open");
                event.stopImmediatePropagation();
                return false;
            }
        }
        return undefined;
    }

    /**
     * Returns whether a move is allowed.
     *
     * @param {Object} move
     *
     * @returns {Boolean}
     */
    function isLegalMove(move) {
        var sourceLevels, targetLevels, extraLevels, totalLevels, allowed;

        if (typeof move.r !== "object") {
            return false;
        }
        sourceLevels = childLevels(move.o);
        targetLevels = level(move.r);
        // paste vs. dnd:
        extraLevels = move.p === "last" || move.p === "inside" ? 1 : 0;
        totalLevels = sourceLevels + targetLevels + extraLevels;
        allowed =  totalLevels <= PAGEMANAGER.menuLevels;
        if (!allowed && !move.ot.data.dnd.active && PAGEMANAGER.verbose) {
            alert(PAGEMANAGER.menuLevelMessage);
        }
        return allowed;
    }

    function contextMenuItems() {
        return {
            "create": {
                "label": PAGEMANAGER.createOp,
                "action": function (obj) {
                    doCreate(obj.reference);
                }
            },
            "create-after": {
                "label": PAGEMANAGER.createAfterOp,
                "action": function (obj) {
                    doCreateAfter(obj.reference);
                }
            },
            "rename": {
                "label": PAGEMANAGER.renameOp,
                "action": function (obj) {
                    doRename(obj.reference);
                }
            },
            "remove": {
                "label": PAGEMANAGER.deleteOp,
                "action": function (obj) {
                    doDelete(obj.reference);
                }
            },
            "cut": {
                "label": PAGEMANAGER.cutOp,
                "separator_before": true,
                "action": function (obj) {
                    doCut(obj.reference);
                }
            },
            "copy": {
                "label": PAGEMANAGER.copyOp,
                "action": function (obj) {
                    doCopy(obj.reference);
                }
            },
            "paste": {
                "label": PAGEMANAGER.pasteOp,
                "action": function (obj) {
                    doPaste(obj.reference);
                }
            },
            "paste-after": {
                "label": PAGEMANAGER.pasteAfterOp,
                "action": function (obj) {
                    doPasteAfter(obj.reference);
                }
            }
        };
    }

    /**
     * Marks duplicate page headings as such.
     *
     * @param {} node
     * @param {Number} duration
     *
     * @returns {Number} The number of duplicate pages.
     */
    function markDuplicates(node, duplicates) {
        var children, i, j, iText, jText, heading;

        children = widget.get_children_dom(node);
        for (i = 0; i < children.length; i += 1) {
            duplicates = markDuplicates(children[i], duplicates);
            iText = widget.get_text(children[i]);
            for (j = i + 1; j < children.length; j += 1) {
                jText = widget.get_text(children[j]);
                if (iText === jText) {
                    duplicates += 1;
                    heading = PAGEMANAGER.duplicateHeading + " " + duplicates;
                    widget.rename_node(children[j], heading);
                    console.log(iText, jText);
                }
            }
        }
        return duplicates;
    }

    /**
     * Restores the page headings.
     *
     * @param {Element} node
     *
     * @returns {undefined}
     */
    function restorePageHeadings(node) {
        var children, i, child;
return;
        children = widget.get_children_dom(node);
        children.each(function (index, child) {
console.log(child)
            widget.set_text(child, child.title);
            restorePageHeadings(child);
        });
    }

    /**
     * Initializes the confirmation and the alert dialogs.
     *
     * @returns {undefined}
     */
    function initDialogs() {
        var buttons = {};

        $("#pagemanager_confirmation").dialog({
            "autoOpen": false,
            "modal": true
        });

        buttons[PAGEMANAGER.okButton] = function () {
            $(this).dialog("close");
        };
        $("#pagemanager_alert").dialog({
            "autoOpen": false,
            "modal": true,
            "buttons": buttons
        });
    }

    /**
     * Alert an Ajax error.
     *
     * @returns {undefined}
     */
    /*jslint unparam:true*/
    function alertAjaxError(jqXHR, textStatus, errorThrown) {
        window.alert(errorThrown);
    }
    /*jslint unparam:false*/

    /**
     * Initialiazes the plugin.
     *
     * @returns {undefined}
     */
    init = function () {
        var config, events, ids;

        if (typeof $.jstree === "undefined") {
            window.alert(PAGEMANAGER.offendingExtensionError);
            return;
        }
        element = $("#pagemanager");

        initDialogs();

        element.on("loaded.jstree", function () {
            var events;

            if ($("#pagemanager_structure_warning").length === 0) {
                $("#pagemanager_save, #pagemanager_submit").show();
            }
            //markDuplicates(-1, 0);
            if (PAGEMANAGER.hasCheckboxes) {
                checkPages("#");
            }
            events = "move_node.jstree create_node.jstree rename_node.jstree" +
                " delete_node.jstree check_node.jstree uncheck_node.jstree";
            element.on(events, function () {
                modified = true;
            });
        //    element.bind("before.jstree", function (e, data) {
        //        switch (data.func) {
        //        case "create_node":
        //            return beforeCreateNode(e, data);
        //        case "rename":
        //            return beforeRename(e, data);
        //        case "remove":
        //            return beforeRemove(e, data);
        //        default:
        //            return undefined;
        //        }
        //    });
        });

        if (PAGEMANAGER.hasCheckboxes) {
            element.on("check_node.jstree", function (e, data) {
                widget.get_node(data.node.li_attr, true).attr("data-pdattr", "1");
            });
            element.on("uncheck_node.jstree", function (e, data) {
                widget.get_node(data.node.li_attr, true).attr("data-pdattr", "0");
            });
        }

        element.on("create_node.jstree", function (e, data) {
            widget.set_type(data.node, "new");
            widget.check_node(data.node);
        });

        var onRenameNode = (function (e, data) {
            widget.get_node(data.node.li_attr, true).prop("title", data.text);
        });
        element.on("rename_node.jstree", onRenameNode);

        element.on("copy_node.jstree", markCopiedPages);

        //events = "rename_node.jstree delete_node.jstree move_node.jstree";
        //var remarkDuplicates = (function () {
        //    element.off("rename_node.jstree", onRenameNode);
        //    element.off(events, remarkDuplicates);
        //    restorePageHeadings("#");
        //    markDuplicates("#", 0);
        //    element.on(events, remarkDuplicates);
        //    element.on("rename_node.jstree", onRenameNode);
        //});
        //element.on(events, remarkDuplicates);

        if (!window.opera) {
            window.onbeforeunload = function () {
                if (modified && $("#pagemanager_json").val() === "") {
                    return PAGEMANAGER.leaveWarning;
                }
                return undefined;
            };
        } else {
            $(window).unload(function () {
                if (modified && $("#pagemanager_json").val() === "") {
                    if (window.confirm(PAGEMANAGER.leaveConfirmation)) {
                        submit();
                    }
                }
            });
        }

        /*
         * Initialize jsTree.
         */
        config = {
            "plugins": [
                "crrm",
            ],
            "crrm": {
                "move": {
                    "check_move": isLegalMove
                }
            },
        };
        config = {
            "plugins": ["contextmenu", "dnd", "types"],
            "core": {
                "animation": PAGEMANAGER.animation,
                "check_callback": true,
                "data": {
                    "url": PAGEMANAGER.dataURL,
                    "error": alertAjaxError
                },
                "multiple": false,
                "strings": {
                    "Loading ...": PAGEMANAGER.loading
                },
                "themes": {
                    "name": PAGEMANAGER.theme
                }
            },
            "checkbox": {
                "three_state": false,
                "tie_selection": false,
                "whole_node": false
            },
            "contextmenu": {
                "show_at_node": false,
                "select_node": true,
                "items": contextMenuItems
            },
            "types": {
                "new": {
                    "icon": PAGEMANAGER.imageDir + "new.png"
                },
                "default": {}
            }
        };
        if (PAGEMANAGER.hasCheckboxes) {
            config.plugins.push("checkbox");
        }
        element.jstree(config);
        widget = $.jstree.reference("#pagemanager");

        ids = "#pagemanager_save, #pagemanager_expand, #pagemanager_collapse," +
            "#pagemanager_create, #pagemanager_create_after," +
            "#pagemanager_rename, #pagemanager_delete, #pagemanager_cut," +
            "#pagemanager_copy, #pagemanager_paste, #pagemanager_paste_after";
        $(ids).off("click").click(function () {
            tool(this.id.substr(12));
        });

        //$("#pagemanager_form").off("submit").submit(function (event) {
        //    event.preventDefault();
        //    submit();
        //});
        $("#pagemanager_structure_warning button").click(confirmStructureWarning);
    };

    $(init);

}(jQuery));
