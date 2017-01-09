jQuery(function($) {
  $.entwine('ss', function($) {
    $('.arbitrarysettingslist:not(.readonly)').entwine({
      Source: null,
      onmatch : function() {
        this.setSource($(this).data('source'));
        if (typeof this.getSource() == 'undefined') {
          return false;
        }
        this.redraw();
        this._super();
      },
      redraw: function() {
        var _self = this,
          source = this.getSource();
        $.each(source.options, function(key, data) {
          var $li = $('<li></li>');
          var $hidden = $('<input>')
            .addClass('wtksettingfield keyfield')
            .attr('type', 'hidden')
            .attr('name', source.keyName)
            .attr('value', key);

          $li.append($hidden);

          var $label = $('<label></label>').html((typeof data.label !== 'undefined') ? data.label : key);
            $li.append($label);
          var $select = $('<select></select>')
            .addClass('wtksettingsfield valuefield')
            .attr('id', source.formId + '__' + key)
            .attr('name', source.valueName)

          var currentValue = data.default;
          if (typeof data.currentValue !== 'undefined') {
            currentValue = data.currentValue;
          }
          $.each(data.options, function(val, label) {
            var $option = $('<option></option>')
              .html(label)
              .val(val);
            if (currentValue == val) {
              $option.attr('selected', true);
            }
            $select.append($option);
          });
          $li.append($select);
          _self.append($li);
        });
      }
    });
  });
}(jQuery));