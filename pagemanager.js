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
            parent = jstree.get_parent(parent);
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
        var model = jstree.get_json(node, {"no_state": true, "no_id": true, "no_data": true, "no_li_attr": true, "no_a_attr": true});
        return childLevels(model, 0);
    }

    function initCommands() {
        commands = ({
            add: (function (node) {
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
            paste: (function (node) {
                var node = jstree.get_node(node);
                var parent = jstree.get_node(node.parent);
                var pos = $.inArray(node.id, parent.children);
                jstree.paste(parent, pos + 1);
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
    function tool(operation) {
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

    function contextMenuItems(node) {
        var tools = ({
            add: {},
            rename: ({_disabled: /unrenameable$/.test(jstree.get_type(node))}),
            remove: ({_disabled: jstree.get_children_dom("#").length < 2}),
            cut: ({separator_before: true}),
            copy: {},
            paste: ({_disabled: !jstree.can_paste()}),
            edit: ({separator_before: true, _disabled: !jstree.get_node(node, true).attr("data-url")}),
            preview: ({_disabled: !jstree.get_node(node, true).attr("data-url")})
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
     * @param {} deleted
     *
     * @returns {Number} The number of duplicate pages.
     */
    function markDuplicates(node, deleted) {
        var children = jstree.get_children_dom(node);
        if (deleted) {
            children = children.not("#" + deleted.id);
        }
        children.each(function (index, value) {
            var text1 = jstree.get_text(value);
            if (index === 0) {
                var type = jstree.get_type(value).replace(/^duplicate-/, '');
                jstree.set_type(value, type);
            }
            for (var i = index + 1; i < children.length; i++) {
                var text2 = jstree.get_text(children[i]);
                var type = jstree.get_type(children[i]).replace(/^duplicate-/, '');                    
                if (text2 === text1) {
                    jstree.set_type(children[i], "duplicate-" + type);
                } else {
                    jstree.set_type(children[i], type);
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
                    icon: PAGEMANAGER.imageDir + "new.png"
                },
                unrenameable: ({
                    icon: PAGEMANAGER.imageDir + "unrenameable.png"
                }),
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
                var id = data.original.id + "_copy_" + (new Date).getTime();
                jstree.set_id(data.node, id);
                jstree.get_node(data.node, true).attr("aria-labelledby", id);
                if (jstree.is_checked(data.original)) {
                    jstree.check_node(data.node);
                } else {
                    jstree.uncheck_node(data.node);
                }
                markCopiedPages(event, data);
            })   
            .on("rename_node.jstree copy_node.jstree move_node.jstree", function (e, data) {
                markDuplicates(data.node.parent);
            })
            .on("delete_node.jstree", function (e, data) {
                markDuplicates(data.node.parent, data.node);
            })
            .on("select_node.jstree", function (e, data) {
                nodeTools.prop("disabled", false);
                $("#pagemanager_rename").prop("disabled", /unrenameable$/.test(jstree.get_type(data.node)));
                $("#pagemanager_remove").prop("disabled", jstree.get_children_dom("#").length < 2);
                $("#pagemanager_paste").prop("disabled", !jstree.can_paste());
                $("#pagemanager_edit, #pagemanager_preview").prop("disabled", !jstree.get_node(data.node, true).attr("data-url"));
            })
            .on("deselect_node.jstree delete_node.jstree", function (e, data) {
                nodeTools.prop("disabled", true);
            })
            .on("cut.jstree copy.jstree", function (e, data) {
                $("#pagemanager_paste").prop("disabled", !jstree.can_paste());
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

        $("#pagemanager_toolbar button").click(function () {
            tool(this.id.substr(12));
        });

        $("#pagemanager_form").submit(function (event) {
            event.preventDefault();
            submit();
        });
    });

}(jQuery));
