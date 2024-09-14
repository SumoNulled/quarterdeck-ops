<?php
namespace HTML;

class FormElement
{
    /**
     * Render an input box with customizable name and label text
     *
     * @param string $name  The name attribute of the input field
     * @param string $label The label text to display
     */
    public function renderInput(string $name, string $label)
    {
        // Output the HTML with dynamic name and label
        echo '
        <div class="col-sm-2">
            <div class="form-group form-float">
                <div class="form-line">
                    <input name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" type="text" class="form-control">
                    <label class="form-label">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</label>
                </div>
            </div>
        </div>';
    }

    /**
     * Render a select dropdown with customizable options
     *
     * @param string $name       The name attribute of the select field
     * @param array  $options    An associative array of options (value => display text)
     * @param string $default    The default selected option value
     * @param string $label      The label text to display above the select box
     * @param string $class      Additional CSS classes for the select element
     */
    public function renderSelect(string $name, array $options, string $default = '', string $label = '', string $class = 'form-control show-tick')
    {
        // Output the HTML with dynamic name, options, and other attributes
        echo '
        <div class="col-sm-3">
            <div class="form-group form-float">
                <label class="form-label"></label>
                <select name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '">
                    <option value="">'. htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</option>';

        foreach ($options as $value => $text) {
            $selected = $value == $default ? ' selected' : '';
            echo '<option value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"' . $selected . '>' . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '</option>';
        }

        echo '
                </select>
            </div>
        </div>';
    }
}
?>
