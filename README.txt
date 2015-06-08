The WordPress plugin extends Z-Ray to display all the details about WordPress code elements, with information about crons, cached objects, plugins and themes, hooks and additional profiling information that is useful during development.
The WordPress plugin also defines the routing logic for WordPress requests - for better events aggregation in Zend Server, and improved results for URLs in URL Insight.

- **Dashboard**: provides useful information about the WordPress installation, including version, whether debug mode is enabled, the used template, and crons status.
- **Cache Objects**: lists all the cached WordPress objects on the page, including their name and size.
- **Plugins**: helps you understand which plugin is consuming the most resources by specifying all the different plugins enabled on the page, together with the time they took to load.
- **Theme Profiler**: helps profile the WordPress theme loaded on the page by breaking down the functions and classes and the time they took to execute
- **Hooks**: outlines all the WordPress hooks triggered during execution. See the name of the hook, it's type (action/filter), the file path, and the time it took to execute.
- **WP Query**: displays the current main WordPress query (e.g. the post query).
- **Crons**: gives insight into the WordPress cron system. View the hooked functions used, their schedule, any defined arguments for the cron event, and the time of the next execution.