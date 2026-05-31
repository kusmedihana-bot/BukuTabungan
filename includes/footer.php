        </div><!-- .page-body -->
    </main>
</div><!-- .app-wrapper -->
<?php $depth = substr_count(str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME'])), '/');
      $root  = str_repeat('../', max(0, $depth - 1)); ?>
<script src="<?= $root ?>assets/js/app.js"></script>
</body>
</html>
