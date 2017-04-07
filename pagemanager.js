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
        modified = false,
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
                // TODO: optimization: fix structure instead of reloading
                jstree.destroy();
                init();
                jstree.restore_state();
            })
            .fail(alertAjaxError);
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
            paste: {},
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
     *
     * @returns {Number} The number of duplicate pages.
     */
    function markDuplicates(node) {
        var children = jstree.get_children_dom(node);
        if (!children) {
            return;
        }
        children.each(function (index, value) {
            var text1 = jstree.get_text(value);
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

    /**
     * Initialiazes the plugin.
     *
     * @returns {undefined}
     */
    init = function () {
        var events;

        if (typeof $.jstree === "undefined") {
            alert(PAGEMANAGER.offendingExtensionError);
            return;
        }
        $("#pagemanager_save, #pagemanager_submit").hide();

        treeview = $("#pagemanager");
        treeview.jstree(getConfig());
        jstree = $.jstree.reference(treeview);

        initCommands();

        treeview.on("ready.jstree", function () {
            var events;

            if ($("#pagemanager_structure_warning").length === 0) {
                $("#pagemanager_save, #pagemanager_submit").show();
            }
            events = "move_node.jstree create_node.jstree rename_node.jstree" +
                " remove_node.jstree check_node.jstree uncheck_node.jstree";
            treeview.on(events, function () {
                modified = true;
            });
            markDuplicates("#");
        });

        treeview.on("open_node.jstree", function (e, data) {
            markDuplicates(data.node);
        });

        treeview.on("create_node.jstree", function (e, data) {
            jstree.set_type(data.node, "new");
            jstree.check_node(data.node);
        });

        treeview.on("copy_node.jstree", function (e, data) {
            var id = data.original.id + "_copy_" + (new Date).getTime();
            jstree.set_id(data.node, id);
            jstree.get_node(data.node, true).attr("aria-labelledby", id);
            if (jstree.is_checked(data.original)) {
                jstree.check_node(data.node);
            } else {
                jstree.uncheck_node(data.node);
            }
            markCopiedPages(event, data);
        });

        treeview.on("rename_node.jstree remove_node.jstree copy_node.jstree move_node.jstree", function (e, data) {
            markDuplicates(data.node.parent);
        });

        var nodeTools = $("#pagemanager_add, #pagemanager_rename, #pagemanager_remove," +
                          "#pagemanager_cut, #pagemanager_copy, #pagemanager_paste," +
                          "#pagemanager_edit, #pagemanager_preview");
        nodeTools.prop("disabled", true);
        treeview.on("select_node.jstree", function (e, data) {
            nodeTools.prop("disabled", false);
            $("#pagemanager_rename").prop("disabled", /unrenameable$/.test(jstree.get_type(data.node)));
            $("#pagemanager_remove").prop("disabled", jstree.get_children_dom("#").length < 2);
            $("#pagemanager_edit, #pagemanager_preview").prop("disabled", !jstree.get_node(data.node, true).attr("data-url"));
        });
        treeview.on("deselect_node.jstree delete_node.jstree", function (e, data) {
            nodeTools.prop("disabled", true);
        });

        $(window).on("beforeunload", function () {
            if (modified && $("#pagemanager_json").val() === "") {
                return PAGEMANAGER.leaveWarning;
            }
            return undefined;
        });

        $("#pagemanager_toolbar button").off("click").click(function () {
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
