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

(function ($) {
    "use strict";

    var element = null,
        widget = null,
        modified = false,
        init;

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
        var json = JSON.stringify(widget.get_json("#", {
            no_state: true, no_data: true, no_a_attr: true
        }));
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
                widget.restore_state();
            }
        }

        widget.save_state();
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

    function checkCallback(operation, node, parent, position, more) {
        switch (operation) {
            case "delete_node":
                return !PAGEMANAGER.verbose || confirm(PAGEMANAGER.confirmDeletionMessage);
            case "move_node":
            case "copy_node":
                return getLevel(parent) + getChildLevels(node) + (!more.pos || more.pos === "i" ? 1 : 0) <= PAGEMANAGER.menuLevels;
            default:
                return true;
        }
    }

    function getLevel(node) {
        var parent = node;
        var level = 0;
        while (parent && parent !== "#") {
            parent = widget.get_parent(parent);
            level++;
        }
        return level;
    }

    function getChildLevels(node) {
        var childLevels = (function (model, acc) {
            if (!model.children.length) {
                return acc;
            } else {
                return Math.max.apply(null, $.map(model.children, function (value) {
                    var levels = childLevels(value, acc + 1);
                    return levels;
                }));
            }
        });
        var model = widget.get_json(node, {"no_state": true, "no_id": true, "no_data": true, "no_li_attr": true, "no_a_attr": true});
        return childLevels(model, 0);
    }

    var commands = {
        add: function (node) {
            var node = widget.get_node(node);
            var parent = widget.get_node(node.parent);
            var pos = $.inArray(node.id, parent.children);
            var id = widget.create_node(parent, PAGEMANAGER.newNode, pos + 1);
            widget.edit(id);
        },
        rename: function (node) {
            widget.edit(node);
        },
        remove: function (node) {
            widget.delete_node(node);
        },
        cut: function (node) {
            widget.cut(node);
        },
        copy: function (node) {
            widget.copy(node);
        },
        paste: function (node) {
            var node = widget.get_node(node);
            var parent = widget.get_node(node.parent);
            var pos = $.inArray(node.id, parent.children);
            widget.paste(parent, pos + 1);
        },
        edit: function (node) {
            widget.save_state();
            location.href = widget.get_node(node, true).attr("data-url") + "&edit";
        },
        preview: function (node) {
            widget.save_state();
            location.href = widget.get_node(node, true).attr("data-url") + "&normal";
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
            case "toggle":
                var collapsed = true;
                widget.get_children_dom("#").each(function (element) {
                    if (widget.is_open(this)) {
                        collapsed = false;
                    }
                });
                if (collapsed) {
                    widget.open_all();
                } else {
                    widget.close_all();
                }
                return;
            case "save":
                submit();
                return;
            case "help":
                open(PAGEMANAGER.userManual, "_blank");
                return;
            default:
                commands[operation](widget.get_selected());
            }
    }

    function contextMenuItems(node) {
        var tools = ({
            "add": {},
            "rename": {"_disabled": /unrenameable$/.test(widget.get_type(node))},
            "remove": {"_disabled": widget.get_children_dom("#").length < 2},
            "cut": {"separator_before": true},
            "copy": {},
            "paste": {},
            "edit": {"separator_before": true},
            "preview": {}
        });
        $.each(tools, function (name, value) {
            value.label = PAGEMANAGER[name + "Op"];
            value.action = (function (obj) {
                commands[name](obj.reference);
            });
            value.icon = PAGEMANAGER.imageDir + name + ".png";
        });
        return tools;
    }

    /**
     * Marks duplicate page headings as such.
     *
     * @param {} node
     *
     * @returns {Number} The number of duplicate pages.
     */
    function markDuplicates(node) {
        var children = widget.get_children_dom(node);
        if (!children) {
            return;
        }
        children.each(function (index, value) {
            var text1 = widget.get_text(value);
            for (var i = index + 1; i < children.length; i++) {
                var text2 = widget.get_text(children[i]);
                var type = widget.get_type(children[i]).replace(/^duplicate-/, '');                    
                if (text2 === text1) {
                    widget.set_type(children[i], "duplicate-" + type);
                } else {
                    widget.set_type(children[i], type);
                }
            }
        });
    }

    /**
     * Alert an Ajax error.
     *
     * @returns {undefined}
     */
    function alertAjaxError(jqXHR, textStatus, errorThrown) {
        alert(errorThrown);
    }

    /**
     * Initialiazes the plugin.
     *
     * @returns {undefined}
     */
    init = function () {
        var config, events, ids;

        if (typeof $.jstree === "undefined") {
            alert(PAGEMANAGER.offendingExtensionError);
            return;
        }
        $("#pagemanager_save, #pagemanager_submit").hide();
        element = $("#pagemanager");

        element.on("ready.jstree", function () {
            var events;

            if ($("#pagemanager_structure_warning").length === 0) {
                $("#pagemanager_save, #pagemanager_submit").show();
            }
            if (PAGEMANAGER.hasCheckboxes) {
                checkPages("#");
            }
            events = "move_node.jstree create_node.jstree rename_node.jstree" +
                " remove_node.jstree check_node.jstree uncheck_node.jstree";
            element.on(events, function () {
                modified = true;
            });
        });

        if (PAGEMANAGER.hasCheckboxes) {
            element.on("check_node.jstree", function (e, data) {
                widget.get_node(data.node.li_attr, true).attr("data-pdattr", "1");
            });
            element.on("uncheck_node.jstree", function (e, data) {
                widget.get_node(data.node.li_attr, true).attr("data-pdattr", "0");
            });
        }

        element.on("open_node.jstree", function (e, data) {
            markDuplicates(data.node);
        });

        element.on("create_node.jstree", function (e, data) {
            widget.set_type(data.node, "new");
            widget.check_node(data.node);
        });

        element.on("copy_node.jstree", markCopiedPages);

        element.on("rename_node.jstree remove_node.jstree move_node.jstree", function (e, data) {
            markDuplicates(data.node.parent);
        });

        var nodeTools = $("#pagemanager_add, #pagemanager_rename, #pagemanager_remove," +
                          "#pagemanager_cut, #pagemanager_copy, #pagemanager_paste," +
                          "#pagemanager_edit, #pagemanager_preview");
        nodeTools.prop("disabled", true);
        element.on("select_node.jstree", function (e, data) {
            nodeTools.prop("disabled", false);
            $("#pagemanager_rename").prop("disabled", /unrenameable$/.test(widget.get_type(data.node)));
            $("#pagemanager_remove").prop("disabled", widget.get_children_dom("#").length < 2);
        });
        element.on("deselect_node.jstree delete_node.jstree", function (e, data) {
            nodeTools.prop("disabled", true);
        });

        $(window).on("beforeunload", function () {
            if (modified && $("#pagemanager_json").val() === "") {
                return PAGEMANAGER.leaveWarning;
            }
            return undefined;
        });

        /*
         * Initialize jsTree.
         */
        config = {
            "plugins": ["contextmenu", "dnd", "state", "types"],
            "core": {
                "animation": PAGEMANAGER.animation,
                "check_callback": checkCallback,
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
            "state": {
                "key": PAGEMANAGER.stateKey,
                "events": "",
                "filter": (function (state) {
                    delete state.checkbox;
                    return state;
                })
            },
            "types": {
                "new": {
                    "icon": PAGEMANAGER.imageDir + "new.png"
                },
                "unrenameable": {
                    "icon": PAGEMANAGER.imageDir + "unrenameable.png"
                },
                "duplicate-default": {
                    "icon": PAGEMANAGER.imageDir + "duplicate.png"
                },
                "duplicate-new": {
                    "icon": PAGEMANAGER.imageDir + "duplicate.png"
                },
                "duplicate-unrenameable": {
                    "icon": PAGEMANAGER.imageDir + "duplicate.png"
                },
                "default": {}
            }
        };
        if (PAGEMANAGER.hasCheckboxes) {
            config.plugins.push("checkbox");
        }
        element.jstree(config);
        widget = $.jstree.reference("#pagemanager");
        markDuplicates("#");
        ids = "#pagemanager_save, #pagemanager_toggle, #pagemanager_add, #pagemanager_rename, #pagemanager_remove," +
            "#pagemanager_cut, #pagemanager_copy, #pagemanager_edit, #pagemanager_preview, #pagemanager_paste, #pagemanager_help";
        $(ids).off("click").click(function () {
            tool(this.id.substr(12));
        });

        $("#pagemanager_form").off("submit").submit(function (event) {
            event.preventDefault();
            submit();
        });
        $("#pagemanager_structure_warning button").click(confirmStructureWarning);
    };

    $(init);

}(jQuery));
