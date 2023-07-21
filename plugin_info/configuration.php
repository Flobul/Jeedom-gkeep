
<?php
   /* This file is part of Jeedom.
    *
    * Jeedom is free software: you can redistribute it and/or modify
    * it under the terms of the GNU General Public License as published by
    * the Free Software Foundation, either version 3 of the License, or
    * (at your option) any later version.
    *
    * Jeedom is distributed in the hope that it will be useful,
    * but WITHOUT ANY WARRANTY; without even the implied warranty of
    * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    * GNU General Public License for more details.
    *
    * You should have received a copy of the GNU General Public License
    * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
    */

   require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
   include_file('core', 'authentification', 'php');
   if (!isConnect()) {
       include_file('desktop', '404', 'php');
       die();
   }
   $plugin = plugin::byId('gkeep');
   sendVarToJS('version', gkeep::$_pluginVersion);
   $token = config::byKey('token', 'gkeep');
   ?>

<form class="form-horizontal">
   <fieldset>
      <legend>
         <i class="fa fa-list-alt"></i> {{Général}}
      </legend>
      <div class="form-group">
         <?php
            $update = $plugin->getUpdate();
            if (is_object($update)) {
                echo '<div class="col-lg-3">';
                echo '<div>';
                echo '<label>{{Branche}} :</label> '. $update->getConfiguration('version', 'stable');
                echo '</div>';
                echo '<div>';
                echo '<label>{{Source}} :</label> ' . $update->getSource();
                echo '</div>';
                echo '<div>';
                echo '<label>{{Version}} :</label> v' . ((gkeep::$_pluginVersion)?gkeep::$_pluginVersion:' '). ' (' . $update->getLocalVersion() . ')';
                echo '</div>';
                echo '</div>';
            }
            ?>
         <div class="col-lg-5">
            <div>
               <i><a class="btn btn-primary btn-xs" target="_blank" href="https://flobul-domotique.fr/presentation-du-plugin-gkeep-pour-jeedom/"><i class="fas fa-book"></i><strong> {{Présentation du plugin}}</strong></a></i>
               <i><a class="btn btn-success btn-xs" target="_blank" href="<?=$plugin->getDocumentation()?>"><i class="fas fa-book"></i><strong> {{Documentation complète du plugin}}</strong></a></i>
            </div>
            <div>
               <i> {{Les dernières actualités du plugin}} <a class="btn btn-label btn-xs" target="_blank" href="https://community.jeedom.com/t/plugin-gkeep-documentation-et-actualites/39994"><i class="icon jeedomapp-home-jeedom icon-gkeep"></i><strong>{{sur le community}}</strong></a>.</i>
            </div>
            <div>
               <i> {{Les dernières discussions autour du plugin}} <a class="btn btn-label btn-xs" target="_blank" href="https://community.jeedom.com/tags/plugin-gkeep"><i class="icon jeedomapp-home-jeedom icon-gkeep"></i><strong>{{sur le community}}</strong></a>.</i></br>
               <i> {{Pensez à mettre le tag}} <b><font font-weight="bold" size="+1">#plugin-gkeep</font></b> {{et à fournir les log dans les balises préformatées}}.</i>
            </div>
            <style>
               .icon-gkeep {
                   font-size: 1.3em;
                   color: #94CA02;
               }

               :root{
                 --background-color: #1987ea;
                }
            </style>
         </div>
      </div>
      <div class="form-group">
        <legend>
		<i class="fas fa-cogs"></i> {{Paramètres}}
		</legend>
          <div class="form-group">
              <label class="col-lg-4 control-label">{{Intervalle de rafraîchissement des informations (cron)}}
      <sup><i class="fas fa-question-circle" title="{{Sélectionnez l'intervalle auquel le plugin ira récupérer les informations sur les serveurs Creality.}}"></i></sup>
              </label>
              <div class="col-lg-4">
                  <select class="configKey form-control" data-l1key="autorefresh" >
                      <option value="* * * * *">{{Toutes les minutes}}</option>
                      <option value="*/2 * * * *">{{Toutes les 2 minutes}}</option>
                      <option value="*/3 * * * *">{{Toutes les 3 minutes}}</option>
                      <option value="*/4 * * * *">{{Toutes les 4 minutes}}</option>
                      <option value="*/5 * * * *">{{Toutes les 5 minutes}}</option>
                      <option value="*/10 * * * *">{{Toutes les 10 minutes}}</option>
                      <option value="*/15 * * * *">{{Toutes les 15 minutes}}</option>
                      <option value="*/30 * * * *">{{Toutes les 30 minutes}}</option>
                      <option value="*/45 * * * *">{{Toutes les 45 minutes}}</option>
                      <option value="">{{Jamais}}</option>
                  </select>
              </div>
          </div>
      </div>

      <div class="form-group">
		<legend>
		  <i class="fas fa-user-cog"></i> {{Authentification}}
		</legend>
        <div class="form-group">
          <label class="col-sm-2 control-label"><strong> {{Nombre de comptes}}</strong>
              <sup><i class="fas fa-question-circle" title="{{Entrez le nombre maximum de compte à synchroniser.}}"></i></sup>
          </label>
          <div class="col-sm-1">
            <input type="number" min="1" max="10" class="configKey form-control" data-l1key="max_account_number" placeholder="1"></input>
          </div>
        </div>
      
        <?php for ($i = 1; $i <= config::byKey('max_account_number', 'gkeep'); $i++) { ?>
        <div class="col-sm-6">
          <legend><i class="fab fa-google"></i> {{Compte n°}}<?php echo $i ?></legend>
          <div class="form-group">
            <label class="col-sm-4 control-label"><strong> {{Adresse email}}</strong>
                <sup><i class="fas fa-question-circle" title="{{Entrez l'identifiant.}}"></i></sup>
            </label>
            <div class="col-sm-6">
              <input type="text" class="configKey form-control" data-l1key="email" data-l2key="<?php echo $i ?>" placeholder="mon@email.com"></input>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-4 control-label"><strong> {{Mot de passe}}</strong>
                <sup><i class="fas fa-question-circle" title="{{Entrez le mot de passe.}}"></i></sup>
            </label>
            <div class="input-group col-sm-6">
                <input type="password" class="inputPassword configKey form-control" data-l1key="password" data-l2key="<?php echo $i ?>" placeholder="password">
                <span class="input-group-btn">
                    <a class="btn btn-default form-control bt_showPass roundedRight"><i class="fas fa-eye"></i></a>
                </span>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-4 control-label"><strong> {{Première connexion}}</strong>
                <sup><i class="fas fa-question-circle" title="{{Cliquez ici pour la première connexion.}}"></i></sup>
            </label>
            <div class="col-sm-6">
               <a data-credential="<?php echo $i ?>" class="btn btn-success bt_getCredentials" style="width:30px"><i class="fas fa-user-circle"></i>{{}}</a>
            </div>
          </div>
          <?php if ($token[$i]) { ?>
            <div class="form-group">
              <label class="col-sm-4 control-label"><strong> {{Jeton d'accès}}</strong>
                  <sup><i class="fas fa-question-circle" title="{{Jeton d'accès généré}}"></i></sup>
              </label>
              <div class="col-sm-6">
                <input type="text" text-editable="false" class="configKey form-control disabled" data-l1key="token" data-l2key="<?php echo $i ?>" placeholder="Jeton">
              </div>
            </div>
          <?php } ?>
        </div>
        <?php } ?>
      </div>
   </fieldset>
</form>


<?php include_file('desktop', 'configuration', 'js', 'gkeep'); ?>