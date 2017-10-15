(function ($) {
    var csscls = PhpDebugBar.utils.makecsscls('phpdebugbar-widgets-');

    var formatData = function (data) {
        var obj = JSON.parse(data);

        renderjson.set_icons('+', '-')
            .set_show_to_level(2);

        var rendered = renderjson(obj);
        var container = $('<div id="json-data"/>').append(rendered);

        $(this.$el).html('').append(container);
    };

    PhpDebugBar.Widgets.RenderJsonWidget = PhpDebugBar.Widget.extend({
        className: csscls('synergy-es'),
        render: function () {
            this.bindAttr('data', formatData);
        }
    });

})(PhpDebugBar.$);