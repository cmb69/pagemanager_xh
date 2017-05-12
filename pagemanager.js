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

    var treeview = null,
        jstree = null,
        modified,
        commands,
        init;

    /**
     * Marks new pages as such.
     *
     * @param {Element} node
     *
     * @returns {undefined}
     */
    function markNewPages(node) {
        $.each(node.children, function (index, value) {
            jstree.set_type(value, "new");
            markNewPages(jstree.get_node(value));
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
        jstree.set_type(data.node, "new");
        markNewPages(data.node);
    }

    /**
     * Submits the page structure.
     *
     * @returns {undefined}
     */
    function submit() {
        jstree.save_state();
        var json = JSON.stringify(jstree.get_json("#", {
            no_data: true, no_a_attr: true, no_li_attr: true
        }));
        $("#pagemanager_json").val(json);
        var form = $("#pagemanager_form");
        form.find(".xh_success, .xh_fail").remove();
        var status = $(".pagemanager_status");
        status.show();
        $.post(form.attr("action"), form.serialize())
            .always(function () {
                status.hide();
            })
            .done(function (data) {
                status.after(data);
                jstree.refresh(false, true);
            })
            .fail(alertAjaxError);
    }

    function checkCallback(operation, node, parent, position, more) {
        switch (operation) {
            case "rename_node":
            case "edit":
                return !(/unrenameable/.test(jstree.get_type(node)));
            case "delete_node":
                return !PAGEMANAGER.verbose || confirm(PAGEMANAGER.confirmDeletionMessage);
            default:
                return true;
        }
    }

    function getLevel(node) {
        var parent = node;
        var level = 0;
        while (parent && parent !== "#") {
            parent = jstree.get_parent(parent);
            level++;
        }
        return level;
    }

    function getChildLevels(node) {
        var childLevels = (function (model, acc) {
            if (!model.children || !model.children.length) {
                return acc;
            } else {
                return Math.max.apply(null, $.map(model.children, function (value) {
                    var levels = childLevels(value, acc + 1);
                    return levels;
                }));
            }
        });
        var model = jstree.get_json(node, {"no_state": true, "no_id": true, "no_data": true, "no_li_attr": true, "no_a_attr": true});
        return childLevels(model, 0);
    }

    function getPasteLevel(node) {
        var level = getLevel(node);
        var buffer = jstree.get_buffer();
        if (buffer && buffer.node.length) {
            level += getChildLevels(buffer.node[0]);
        }
        return level;
    }

    function isNodeInBuffer(node) {
        var buffer = jstree.get_buffer();
        return buffer && buffer.node.length && jstree.get_node(buffer.node[0]) === jstree.get_node(node);
    }

    function doPaste(obj, pos) {
        var node;
        var buffer = jstree.get_buffer();
        if (buffer && buffer.node.length) {
            node = buffer.node[0];
        }
        jstree.paste(obj, pos);
        if (node) {
            jstree.copy(node);
        }
    }

    function initCommands() {
        commands = ({
            addBefore: (function (node) {
                var node = jstree.get_node(node);
                var parent = jstree.get_node(node.parent);
                var pos = $.inArray(node.id, parent.children);
                var id = jstree.create_node(parent, PAGEMANAGER.newNode, pos);
                jstree.edit(id);
            }),
            addInside: (function (node) {
                var id = jstree.create_node(node, PAGEMANAGER.newNode);
                jstree.edit(id);
            }),
            addAfter: (function (node) {
                var node = jstree.get_node(node);
                var parent = jstree.get_node(node.parent);
                var pos = $.inArray(node.id, parent.children);
                var id = jstree.create_node(parent, PAGEMANAGER.newNode, pos + 1);
                jstree.edit(id);
            }),
            rename: $.proxy(jstree.edit, jstree),
            remove: $.proxy(jstree.delete_node, jstree),
            cut: $.proxy(jstree.cut, jstree),
            copy: $.proxy(jstree.copy, jstree),
            pasteBefore: (function (node) {
                var node = jstree.get_node(node);
                var parent = jstree.get_node(node.parent);
                var pos = $.inArray(node.id, parent.children);
                doPaste(parent, pos);
            }),
            pasteInside: (function (node) {
                doPaste(node, "last");
            }),
            pasteAfter: (function (node) {
                var node = jstree.get_node(node);
                var parent = jstree.get_node(node.parent);
                var pos = $.inArray(node.id, parent.children);
                doPaste(parent, pos + 1);
            }),
            edit: (function (node) {
                jstree.save_state();
                location.href = jstree.get_node(node, true).attr("data-url") + "&edit";
            }),
            preview: (function (node) {
                jstree.save_state();
                location.href = jstree.get_node(node, true).attr("data-url") + "&normal";
            })
        });
    }

    /**
     * Execute a tool.
     *
     * @param {String} operation
     *
     * @returns {undefined}
     */
    function tool(operation, event) {
        switch (operation) {
            case "toggle":
                var collapsed = true;
                jstree.get_children_dom("#").each(function (element) {
                    if (jstree.is_open(this)) {
                        collapsed = false;
                    }
                });
                if (collapsed) {
                    jstree.open_all();
                } else {
                    jstree.close_all();
                }
                return;
            case "add":
            case "paste":
                var element = $("#pagemanager_" + operation).next();
                element.toggle();
                event.stopPropagation();
                $(document).one("click", $.proxy(element.hide, element, undefined));
                return;
            case "save":
                submit();
                return;
            case "help":
                open(PAGEMANAGER.userManual, "_blank");
                return;
            default:
                commands[operation](jstree.get_selected());
            }
    }

    function contextSubmenuItems(node, op) {
        return [{
            label: PAGEMANAGER.before,
            action: (function (obj) {
                commands[op + "Before"](obj.reference);
            }),
            icon: "fa fa-arrow-up",
            _disabled: op === "paste" && getPasteLevel(node) > 9
        }, {
            label: PAGEMANAGER.inside,
            action: (function (obj) {
                commands[op + "Inside"](obj.reference);
            }),
            icon: "fa fa-arrow-right",
            _disabled: op === "add" ? getLevel(node) >= 9 : getPasteLevel(node) >= 9 || isNodeInBuffer(node)
        }, {
            label: PAGEMANAGER.after,
            action: (function (obj) {
                commands[op + "After"](obj.reference);
            }),
            icon: "fa fa-arrow-down",
            _disabled: op === "paste" && getPasteLevel(node) > 9
        }];
    }

    function contextMenuItems(node) {
        var canPaste = jstree.can_paste();
        var tools = ({
            add: ({submenu: contextSubmenuItems(node, "add")}),
            rename: ({_disabled: /unrenameable$/.test(jstree.get_type(node))}),
            remove: ({_disabled: jstree.get_children_dom("#").length < 2}),
            cut: ({separator_before: true}),
            copy: {},
            paste: ({_disabled: !canPaste}),
            edit: ({separator_before: true, _disabled: !jstree.get_node(node, true).attr("data-url")}),
            preview: ({_disabled: !jstree.get_node(node, true).attr("data-url")})
        });
        if (canPaste) {
            tools.paste.submenu = contextSubmenuItems(node, "paste");
        }
        $.each(tools, function (name, value) {
            value.label = PAGEMANAGER[name + "Op"];
            value.action = (function (obj) {
                commands[name](obj.reference);
            });
            value.icon = "fa " + PAGEMANAGER.classes[name];
        });
        delete tools.add.action;
        delete tools.paste.action;
        return tools;
    }

    /**
     * Marks duplicate page headings as such.
     *
     * @param {} node
     * @param {} deleted
     *
     * @returns {Number} The number of duplicate pages.
     */
    function markDuplicates(node, deleted) {
        var children = jstree.get_children_dom(node);
        if (!children) {
            return;
        }
        if (deleted) {
            children = children.not("#" + deleted.id);
        }
        children.each(function (index) {
            var type = jstree.get_type(this).replace(/^duplicate-/, '');
            jstree.set_type(this, type);
        });
        children.each(function (index) {
            var text1 = jstree.get_text(this);
            for (var i = index + 1; i < children.length; i++) {
                var text2 = jstree.get_text(children[i]);
                var type = jstree.get_type(children[i]);
                if (text2 === text1) {
                    jstree.set_type(children[i], "duplicate-" + type);
                }
            }
        });
        children.each(function () {
            markDuplicates(this, deleted);
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

    function getConfig() {
        var config = ({
            plugins: ["contextmenu", "dnd", "state", "types"],
            core: ({
                animation: PAGEMANAGER.animation,
                check_callback: checkCallback,
                data: ({
                    url: PAGEMANAGER.dataURL,
                    error: alertAjaxError
                }),
                force_text: true,
                multiple: false,
                strings: ({
                    "Loading ...": PAGEMANAGER.loading
                }),
                themes: ({
                    name: PAGEMANAGER.theme,
                    responsive: true
                })
            }),
            checkbox: ({
                three_state: false,
                tie_selection: false,
                whole_node: false
            }),
            contextmenu: ({
                show_at_node: false,
                select_node: true,
                items: contextMenuItems
            }),
            state: ({
                key: PAGEMANAGER.stateKey,
                events: "",
                filter: (function (state) {
                    delete state.checkbox;
                    return state;
                })
            }),
            types: ({
                "new": {
                    icon: "fa fa-folder-open",
                    max_depth: 8
                },
                unrenameable: ({
                    icon: "fa fa-tag",
                    max_depth: 8
                }),
                "duplicate-default": {
                    "icon": "fa fa-exclamation-triangle",
                    max_depth: 8
                },
                "duplicate-new": {
                    "icon": "fa fa-exclamation-triangle",
                    max_depth: 8
                },
                "duplicate-unrenameable": {
                    "icon": "fa fa-exclamation-triangle",
                    max_depth: 8
                },
                "default": {                    
                    icon: "fa fa-folder-open-o",
                    max_depth: 8
                }
            })
        });
        if (PAGEMANAGER.hasCheckboxes) {
            config.plugins.push("checkbox");
        }
        return config;
    };

    $(function () {
        if (typeof $.jstree === "undefined") {
            alert(PAGEMANAGER.offendingExtensionError);
            return;
        }

        (function () {
            var structureWarning = $("#pagemanager_structure_warning");
            if (structureWarning.length) {
                $("#pagemanager_save").hide();
                structureWarning.find("button").click(function () {
                    structureWarning.hide();
                    $("#pagemanager_save").show();
                });
            }
        }());

        treeview = $("#pagemanager");
        treeview.jstree(getConfig());
        jstree = $.jstree.reference(treeview);

        initCommands();

        var nodeTools = $("#pagemanager_add, #pagemanager_rename, #pagemanager_remove," +
                          "#pagemanager_cut, #pagemanager_copy, #pagemanager_paste," +
                          "#pagemanager_edit, #pagemanager_preview");
        var modificationEvents = "move_node.jstree create_node.jstree rename_node.jstree" +
            " delete_node.jstree check_node.jstree uncheck_node.jstree";

        nodeTools.prop("disabled", true);

        treeview
            .on("ready.jstree refresh.jstree", function () {
                modified = false;
                markDuplicates("#");
            })
            .on("refresh.jstree", function () {
                jstree.restore_state();
            })
            .on(modificationEvents, function () {
                modified = true;
            })
            .on("open_node.jstree", function (e, data) {
                markDuplicates(data.node);
            })
            .on("create_node.jstree", function (e, data) {
                jstree.set_type(data.node, "new");
                jstree.check_node(data.node);
            })
            .on("copy_node.jstree", function (e, data) {
                var fixId = (function (node, origId) {
                    var id = origId.replace(/_copy_\d+$/, "") + "_copy_" + (new Date).getTime();
                    jstree.set_id(node, id);
                });
                fixId(data.node, data.original.id);
                $.each(data.node.children_d, function (index) {
                    fixId(this, data.original.children_d[index]);
                });

                var checkNode = (function (node, orig) {
                    if (jstree.is_checked(orig)) {
                        jstree.check_node(node);
                    }
                });
                checkNode(data.node, data.original);
                $.each(data.node.children_d, function (index) {
                    if (jstree.is_checked(data.original.children_d[index])) {
                        jstree.check_node(this);
                    }
                });
                markCopiedPages(e, data);
            })   
            .on("rename_node.jstree copy_node.jstree move_node.jstree", function (e, data) {
                markDuplicates(data.node.parent);
            })
            .on("delete_node.jstree", function (e, data) {
                markDuplicates(data.node.parent, data.node);
            })
            .on("select_node.jstree", function (e, data) {
                nodeTools.prop("disabled", false);
                $("#pagemanager_addInside").prop("disabled", getLevel(data.node) >= 9);
                $("#pagemanager_rename").prop("disabled", /unrenameable$/.test(jstree.get_type(data.node)));
                $("#pagemanager_remove").prop("disabled", jstree.get_children_dom("#").length < 2);
                $("#pagemanager_paste").prop("disabled", !jstree.can_paste());
                $("#pagemanager_pasteBefore, #pagemanager_pasteAfter").prop("disabled", getPasteLevel(data.node) > 9);
                $("#pagemanager_pasteInside").prop("disabled", getPasteLevel(data.node) >= 9 || isNodeInBuffer(data.node));
                $("#pagemanager_edit, #pagemanager_preview").prop("disabled", !jstree.get_node(data.node, true).attr("data-url"));
            })
            .on("deselect_node.jstree delete_node.jstree", function (e, data) {
                nodeTools.prop("disabled", true);
            })
            .on("cut.jstree copy.jstree", function (e, data) {
                $("#pagemanager_paste").prop("disabled", !jstree.can_paste());
                $("#pagemanager_pasteBefore, #pagemanager_pasteAfter").prop("disabled", getPasteLevel(data.node[0]) > 9);
                $("#pagemanager_pasteInside").prop("disabled", getPasteLevel(data.node[0]) >= 9 || isNodeInBuffer(data.node[0]));
            })
            .on("paste.jstree", function () {
                $("#pagemanager_paste").prop("disabled", true);
            });

        $(window).on("beforeunload", function () {
            if (modified) {
                return PAGEMANAGER.leaveWarning;
            }
            return undefined;
        });

        var template = '<div class="pagemanager_tool_inner">' +
            '<button id="pagemanager_%sBefore" type="button" title="' + PAGEMANAGER.before + '"><span class="fa fa-arrow-up fa-lg" aria-hidden="true"></span></button>' +
            '<button id="pagemanager_%sInside" type="button" title="' + PAGEMANAGER.inside + '"><span class="fa fa-arrow-right fa-lg" aria-hidden="true"></span></button>' +
            '<button id="pagemanager_%sAfter" type="button" title="' + PAGEMANAGER.after + '"><span class="fa fa-arrow-down fa-lg" aria-hidden="true"></span></button>' +
            '</div>';
        $("#pagemanager_add, #pagemanager_paste")
            .wrap('<div class="pagemanager_tool_wrapper">');
        $("#pagemanager_add").after(template.replace(/%s/g, "add"));
        $("#pagemanager_paste").after(template.replace(/%s/g, "paste"));
        $("#pagemanager_toolbar button").click(function (event) {
            tool(this.id.substr(12), event);
        });

        $("#pagemanager_form").submit(function (event) {
            event.preventDefault();
            submit();
        });
    });

}(jQuery));
