<ul id="$ID" class="arbitrarysettings-list">
  <% loop $Settings %>
    <li>
      <input type="hidden" name="$Up.KeyName" value="$Key">

      <div class="arbitrarysettings-option">
        <label for="{$Up.ID}_$Key">
          $Label
        </label>

        <select name="$Up.ValueName" id="{$Up.ID}_$Key">
          <% loop $Options %>
            <option value="$Val" <% if $Selected %>selected<% end_if %>>
              $Label
            </div>
          <% end_loop %>
        </select>
      </div>
      <% if $Description %>
        <div class="arbitrarysettings-description">
          $Description.RAW
        </div>
      <% end_if %>
    </li>
  <% end_loop %>
</ul>