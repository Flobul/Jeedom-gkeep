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

const installDate = document.getElementById('span_plugin_install_date');
if (installDate) installDate.textContent = `v${version} (${installDate.textContent})`;

document.querySelector('.bt_refreshPluginInfo')?.insertAdjacentHTML(
  'afterend',
  '<a class="btn btn-success btn-sm" target="_blank" rel="noopener noreferrer" href="https://market.jeedom.com/index.php?v=d&p=market_display&id=4423"><i class="fas fa-comment-dots"></i> {{Donner mon avis}}</a>'
);

const contactMode = document.querySelector('.configKey[data-l1key="mobileormail"]');
function updateContactMode() {
  const useMobile = contactMode?.value === '1';
  document.querySelectorAll('.configKey[data-l1key="idemail"]').forEach(element => element.style.display = useMobile ? 'none' : '');
  document.querySelectorAll('.configKey[data-l1key="idmobile"]').forEach(element => element.style.display = useMobile ? '' : 'none');
}
contactMode?.addEventListener('change', updateContactMode);
updateContactMode();

document.querySelectorAll('.bt_getCredentials').forEach(button => {
  button.addEventListener('click', function() {
    const credential = this.dataset.credential;
    domUtils.ajax({
      type: "POST",
      url: "plugins/gkeep/core/ajax/gkeep.ajax.php",
      data: {
        action: "login",
        id: credential
      },
      dataType: 'json',
      error: function(request, status, error) {
        handleAjaxError(request, status, error);
      },
      success: function(data) {
        if (data.state != 'ok') {
          jeedomUtils.showAlert({
            message: data.result,
            level: 'danger'
          });
          return;
        }
        if (data.result[credential] && data.result[credential].code == 0) {
          jeedomUtils.showAlert({
            message: '{{Authentification réussie.}}',
            level: 'success'
          });
        }
      }
    });
  });
});
