<div class="tk-block">
    <div class="tk-tabs">
        <br id="tk-tab2"/>
        <a href="#tk-tab1"><?php TK_GProject::the_l10n('description'); ?></a>
        <a href="#tk-tab2"><?php TK_GProject::the_l10n('answers'); ?></a>
        <div>
            <?php
            the_post();
            the_content();
            ?>
        </div>
        <div><?php TK_GProject::the_l10n('no_answers'); ?></div>
    </div>
</div>