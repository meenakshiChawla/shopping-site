<?php
get_header(); ?>

<main class="wp-site-blocks">
  <section class="project-archive alignwide">

    <h1 class="archive-title">Our Projects</h1>

    <!-- Filter dropdown -->
    <?php
    $terms = get_terms(['taxonomy' => 'project_type', 'hide_empty' => false]);
    if (!empty($terms)) :
    ?>
      <div id="project-filter">
        <select id="project-type-filter">
          <option value="">All Project Types</option>
          <?php foreach ($terms as $term): ?>
            <option value="<?= esc_attr($term->slug); ?>"><?= esc_html($term->name); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    <?php endif; ?>

    <!-- List Projects -->
    <div id="projects-list">
      <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <div class="project-item">
          <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
          <?php the_excerpt(); ?>
        </div>
      <?php endwhile; else : ?>
        <p>No projects found.</p>
      <?php endif; ?>
    </div>

  </section>
</main>

<?php get_footer(); ?>
