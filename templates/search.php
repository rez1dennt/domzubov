<?php
declare(strict_types=1);

function dz_search_dropdown_template(): string
{
    ob_start();
    ?>
    <div id="site-search" class="dz-search-widget" hidden>
      <form class="dz-search-form" role="search" action="/search" method="get" data-search-form>
        <label class="dz-sr-only" for="site-search-input">Найти услугу, врача или информацию</label>
        <div class="dz-search-field">
          <?= icon('search') ?>
          <input id="site-search-input" name="q" type="search" maxlength="200" autocomplete="off" placeholder="Услуга, симптом или вопрос" role="combobox" aria-autocomplete="list" aria-haspopup="listbox" aria-expanded="false" aria-controls="site-search-results site-search-popular-list site-search-history-list" data-search-input>
          <div class="dz-search-field-controls"><button type="button" data-search-clear aria-label="Очистить поиск" hidden><?= icon('close') ?></button><span class="dz-search-spinner" aria-hidden="true" data-search-spinner></span></div>
          <button class="dz-search-go" type="submit" aria-label="Найти"><?= icon('arrow') ?></button>
        </div>
        <div id="site-search-suggestions" class="dz-search-dropdown" hidden>
          <div class="dz-search-dropdown-head"><span>Поиск по сайту</span><button type="button" data-search-close aria-label="Закрыть поиск"><?= icon('close') ?></button></div>
          <div class="dz-search-scroll">
            <section class="dz-search-history" data-search-history hidden>
              <div class="dz-search-group-title"><span>Вы искали</span><button type="button" data-history-clear>Очистить</button></div>
              <div id="site-search-history-list" role="listbox" aria-label="Вы искали" data-search-history-list></div>
            </section>
            <section id="site-search-popular-list" class="dz-search-popular" role="listbox" aria-label="Часто ищут" data-search-popular>
              <p class="dz-search-group-title">Часто ищут</p>
              <?php foreach(['Болит зуб','Детский стоматолог','Брекеты','Чистка зубов','Имплантация','Адреса клиник','Стоимость лечения'] as $query): ?>
                <button class="dz-search-suggestion" type="button" role="option" aria-selected="false" data-search-query="<?= e($query) ?>"><?= icon('search') ?><span><?= e($query) ?></span><?= icon('arrow') ?></button>
              <?php endforeach; ?>
            </section>
            <ul id="site-search-results" class="dz-search-results" role="listbox" aria-label="Подсказки поиска" data-search-results></ul>
            <div class="dz-search-empty" hidden data-search-empty><p>Ничего не нашли. Попробуйте другое название или опишите, что вас беспокоит.</p><a href="/services">Посмотреть все услуги <?= icon('arrow') ?></a></div>
          </div>
          <div class="dz-search-dropdown-footer"><span data-search-hint></span><button class="dz-search-submit" type="submit" hidden>Все результаты <?= icon('arrow') ?></button></div>
        </div>
        <p class="dz-sr-only" aria-live="polite" data-search-status></p>
      </form>
    </div>
    <?php
    return (string) ob_get_clean();
}
/** @param list<array{url:string,title:string,description:string,type:string}> $results */
function dz_search_page_template(string $query, array $results): string
{
    $query = trim($query);
    ob_start();
    ?>
    <section class="dz-search-page" id="main-content">
      <div class="dz-search-page-inner">
        <p class="dz-search-kicker">Поиск по сайту</p>
        <h1><?= $query === '' ? 'Найдите нужную информацию' : 'Результаты поиска' ?></h1>
        <form class="dz-search-page-form" role="search" action="/search" method="get">
          <label for="search-page-input">Услуга, симптом, врач или адрес</label>
          <div class="dz-search-page-field">
            <input id="search-page-input" name="q" type="search" maxlength="200" value="<?= e($query) ?>" placeholder="Например, болит зуб">
            <button type="submit">Найти</button>
          </div>
        </form>
        <?php if ($query === ''): ?>
          <section class="dz-search-page-empty" aria-labelledby="search-popular-title">
            <h2 id="search-popular-title">Популярные запросы</h2>
            <div class="dz-search-chips">
              <a href="/search?q=<?= rawurlencode('болит зуб') ?>">Болит зуб</a>
              <a href="/search?q=<?= rawurlencode('детский стоматолог') ?>">Детский стоматолог</a>
              <a href="/search?q=<?= rawurlencode('брекеты') ?>">Брекеты</a>
              <a href="/search?q=<?= rawurlencode('цены') ?>">Цены</a>
            </div>
          </section>
        <?php elseif ($results): ?>
          <p class="dz-search-count">Найдено: <?= count($results) ?></p>
          <ol class="dz-search-page-results">
            <?php foreach ($results as $result): ?>
              <li>
                <a href="<?= e($result['url']) ?>">
                  <span class="dz-search-result-type"><?= e($result['type']) ?></span>
                  <strong><?= e($result['title']) ?></strong>
                  <span><?= e($result['description']) ?></span>
                </a>
              </li>
            <?php endforeach ?>
          </ol>
        <?php else: ?>
          <section class="dz-search-page-empty" aria-labelledby="search-empty-title">
            <h2 id="search-empty-title">По запросу «<?= e($query) ?>» ничего не найдено</h2>
            <p>Попробуйте написать короче, описать симптом или перейти к разделам ниже.</p>
            <div class="dz-search-chips">
              <a href="/services">Все услуги</a>
              <a href="/prices">Цены</a>
              <a href="/contacts">Контакты</a>
            </div>
          </section>
        <?php endif ?>
      </div>
    </section>
    <?php
    return (string) ob_get_clean();
}
