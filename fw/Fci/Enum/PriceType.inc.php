<?php
abstract class Fci_Enum_PriceType extends Cemes_Pattern_Enum {}
final class FESTKOSTEN extends Fci_Enum_PriceType { var $value = 'fixed'; }
final class PROZENT extends Fci_Enum_PriceType { var $value = 'percent'; }