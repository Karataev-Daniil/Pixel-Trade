<?php
/*
Template Name: test stend
*/
get_header(); 
?>
<div class="test-palette">
    <h1>🎨 Тестовая палитра переменных + кнопки</h1>

    <?php
    $palette = [
        'gray' => [-6,-5,-4,-3,-2,-1,0,1,2,3,4,5,6],
        'aqua' => [-5,-4,-3,-2,-1,0,1,2,3,4,5],
        'red' => [-5,-4,-3,-2,-1,0,1,2,3,4,5],
        'orange' => [-5,-4,-3,-2,-1,0,1,2,3,4,5],
        'yellow' => [-5,-4,-3,-2,-1,0,1,2,3,4,5],
        'green' => [-5,-4,-3,-2,-1,0,1,2,3,4,5],
        'teal' => [-5,-4,-3,-2,-1,0,1,2,3,4,5],
        'blue' => [-5,-4,-3,-2,-1,0,1,2,3,4,5],
        'violet' => [-5,-4,-3,-2,-1,0,1,2,3,4,5],
        'purple' => [-5,-4,-3,-2,-1,0,1,2,3,4,5],
        'pink' => [-5,-4,-3,-2,-1,0,1,2,3,4,5],
        'success' => [-5,-4,-3,-2,-1,0,1,2,3,4,5],
        'warning' => [-5,-4,-3,-2,-1,0,1,2,3,4,5],
    ];

    foreach ($palette as $group => $values): ?>
        <div class="color-group">
            <h2><?php echo ucfirst($group); ?></h2>
            <div class="color-grid">
                <?php foreach ($values as $val):
                    $var = "--{$group}_{$val}"; ?>
                    <div class="color-box" style="background-color: var(<?php echo $var; ?>);">
                        <?php echo $var; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <h2 class="display-largest">🧩 Примеры кнопок</h2>
    <table class="button-swatch">
        <thead>
            <tr>
                <th>Button Type</th>
                <th>Div Tag</th>
                <th>A Tag</th>
                <th>Button Tag</th>
            </tr>
        </thead>
        <tbody>
            <!-- Primary Buttons -->
            <tr>
                <td>Primary Larger</td>
                <td><div class="primary-button-larger">Primary Larger</div></td>
                <td><a href="#" class="primary-button-larger">Primary Larger</a></td>
                <td><button class="primary-button-larger">Primary Larger</button></td>
            </tr>
            <tr>
                <td>Primary Large</td>
                <td><div class="primary-button-large">Primary Large</div></td>
                <td><a href="#" class="primary-button-large">Primary Large</a></td>
                <td><button class="primary-button-large">Primary Large</button></td>
            </tr>
            <tr>
                <td>Primary Medium</td>
                <td><div class="primary-button-medium">Primary Medium</div></td>
                <td><a href="#" class="primary-button-medium">Primary Medium</a></td>
                <td><button class="primary-button-medium">Primary Medium</button></td>
            </tr>
            <tr>
                <td>Primary Small</td>
                <td><div class="primary-button-small">Primary Small</div></td>
                <td><a href="#" class="primary-button-small">Primary Small</a></td>
                <td><button class="primary-button-small">Primary Small</button></td>
            </tr>

            <!-- Secondary Buttons -->
            <tr>
                <td>Secondary Larger</td>
                <td><div class="secondary-button-larger">Secondary Larger</div></td>
                <td><a href="#" class="secondary-button-larger">Secondary Larger</a></td>
                <td><button class="secondary-button-larger">Secondary Larger</button></td>
            </tr>
            <tr>
                <td>Secondary Small</td>
                <td><div class="secondary-button-small">Secondary Small</div></td>
                <td><a href="#" class="secondary-button-small">Secondary Small</a></td>
                <td><button class="secondary-button-small">Secondary Small</button></td>
            </tr>

            <!-- Tertiary Button -->
            <tr>
                <td>Tertiary Small</td>
                <td><div class="tertiary-button-small">Tertiary Small</div></td>
                <td><a href="#" class="tertiary-button-small">Tertiary Small</a></td>
                <td><button class="tertiary-button-small">Tertiary Small</button></td>
            </tr>

            <!-- Accent Button -->
            <tr>
                <td>Accent Small</td>
                <td><div class="accent-button-small">Accent Small</div></td>
                <td><a href="#" class="accent-button-small">Accent Small</a></td>
                <td><button class="accent-button-small">Accent Small</button></td>
            </tr>
        </tbody>
    </table>
</div>
<?php get_footer(); ?>