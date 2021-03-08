<?php
abstract class Fci_Enum_OptionType extends Cemes_Pattern_Enum {}
final class FELD extends Fci_Enum_OptionType { var $value = 'field'; }
final class BEREICH extends Fci_Enum_OptionType { var $value = 'area'; }
final class DATEI extends Fci_Enum_OptionType { var $value = 'file'; }
final class DROPDOWN extends Fci_Enum_OptionType { var $value = 'drop_down'; }
final class RADIOBUTTONS extends Fci_Enum_OptionType { var $value = 'radio'; }
final class CHECKBOX extends Fci_Enum_OptionType { var $value = 'checkbox'; }
final class MEHRFACHAUSWAHL extends Fci_Enum_OptionType { var $value = 'multiple'; }
final class DATUM extends Fci_Enum_OptionType { var $value = 'date'; }
final class DATUMZEIT extends Fci_Enum_OptionType { var $value = 'date_time'; }
final class ZEIT extends Fci_Enum_OptionType { var $value = 'time'; }