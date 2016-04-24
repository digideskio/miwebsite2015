<?php

$fields = <<<EOT
  <!--div class="fieldset field-grid cf">
    <div class="field field-grid-item field-grid-item-1-2">
      <label class="label" for="form-field-snippet">Zu welchem Zuordungsbegriff soll gesucht werden?</label>
      <div class="field-content">
        <input class="input" type="text" name="tag" autocomplete="on"  data-focus="true" id="form-field-tag">
      </div>
    </div>
  </div-->

  <ul class="input-list field-grid cf">

    <li class="input-list-item field-grid-item field-grid-item-1-4">
      <label class="input input-with-radio" data-focus="true">
        <input id="form-field-studiengang" class="radio" type="radio" autocomplete="on" name="studiengang" value="bachelor">Bachelor
      </label>
    </li>
    <li class="input-list-item field-grid-item field-grid-item-1-4">
      <label class="input input-with-radio">
        <input id="form-field-studiengang" class="radio" type="radio" autocomplete="on" name="studiengang" value="master">Master
      </label>
    </li>
  </ul-->
EOT;

return [
  'params' => [
    'title'  => 'Modulübersicht',
    'desc'   => 'Zeigt die Module eines Studiengangs.',
    'fields' => $fields
  ]
];
