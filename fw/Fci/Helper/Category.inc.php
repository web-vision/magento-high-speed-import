<?php

class Fci_Helper_Category
{
    /**
     * @var Fci_Helper_Category
     */
    protected static $instance;

    /**
     * Singleton method to return just one instance of the class.
     *
     * @return Fci_Helper_Category
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Cleans the given categories and creates them if needed.
     *
     * @param string $categoryList
     * @param array  $roots
     *
     * @return array
     */
    public function cleanCategories($categoryList, $roots)
    {
        $config = Fci_Helper_Config::getInstance();

        $cleanedCategories = [];
        foreach ($roots as $root) {
            $cleanedCategories[$root] = ['id' => $root, 'position' => 0];
        }

        // if there are multiple categories for one product
        $categoryPaths = explode($config->getCategorySeparate(), $categoryList);
        $categoryPaths = array_filter($categoryPaths);

        foreach ($roots as $root) {
            foreach ($categoryPaths as $categoryPath) {
                @list($path, $position) = explode(':', $categoryPath);
                $path = trim($path);
                $position = trim($position);
                if ($path === '') {
                    continue;
                }

                $category = Fci_Helper_Factory_EntityFactory::createCategory(
                    ['path' => $path, 'root_category' => $root]
                );
                if ($category->isNew() && $config->createCategories()) {
                    $category->save();
                }

                if ($id = $category->getId()) {
                    if ($position === '') {
                        $position = $config->getDefaultProductPosition();
                    }
                    $cleanedCategories[$id] = ['id' => $id, 'position' => (int)$position];
                }
            }
        }

        return $cleanedCategories;
    }
}
