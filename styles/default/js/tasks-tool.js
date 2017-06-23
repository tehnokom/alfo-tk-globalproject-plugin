/**
 * @author Sarvaritdinov Ravil
 */

$j = jQuery.noConflict();

function tk_tasks_tool_init() {
    $j(".tk-tasks-list, .tk-tasks-list ul").sortable({connectWith: ".tk-tasks-list, .tk-tasks-list ul",
        dropOnEmpty: true,
        placeholder: "tk-task-empty"});
}
