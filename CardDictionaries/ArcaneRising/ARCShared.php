<?php

  function ARCAbilityCost($cardID)
  {
    global $CS_CharacterIndex, $CS_PlayIndex, $currentPlayer, $combatChain;
    switch($cardID)
    {
      case "ARC003":
        $abilityType = GetResolvedAbilityType($cardID);
        return ($abilityType == "A" ? 1 : 0);
      case "ARC004": return 1;
      case "ARC010":
        $abilityType = GetResolvedAbilityType($cardID);
        return count($combatChain) > 0 ? 0 : 1;
      case "ARC017":
        $items = &GetItems($currentPlayer);
        return ($items[GetClassState($currentPlayer, $CS_PlayIndex) + 1] > 0 ? 0 : 1);
      case "ARC018":
        $abilityType = GetResolvedAbilityType($cardID);
        return count($combatChain) > 0 ? 0 : 1;
      case "ARC040": return 1;
      case "ARC077": return 2;
      case "ARC078": return 2 + NumRunechants($currentPlayer);
      case "ARC079": return 1;
      case "ARC113": case "ARC114": return 3;
      case "ARC115": return 1;
      case "ARC116": return 1;
      case "ARC117": return 0;
      case "ARC113": case "ARC114": return 3;
      case "ARC115": return 1;
      case "ARC116": return 1;
      case "ARC154": return 1;
      default: return 0;
    }
  }

  function ARCAbilityType($cardID, $index=-1)
  {
    global $currentPlayer, $CS_PlayIndex, $combatChain;
    $items = &GetItems($currentPlayer);
    switch($cardID)
    {
      case "ARC003":
        return "A";
      case "ARC004": return "A";
      case "ARC005": return "I";
      case "ARC010":
        if ($index == -1) $index = GetClassState($currentPlayer, $CS_PlayIndex);
        if($index == -1) return "";
        return count($combatChain) > 0 && ClassContains($combatChain[0], "MECHANOLOGIST", $currentPlayer) && CardSubType($combatChain[0]) == "Pistol" && $items[$index + 1] > 0 ? "AR" : "A";
      case "ARC017":
        if($index == -1) $index = GetClassState($currentPlayer, $CS_PlayIndex);
        if (isset($items[$index + 1])) return ($items[$index + 1] > 0 ? "I" : "A");
        else return "A";
      case "ARC018":
        if($index == -1) $index = GetClassState($currentPlayer, $CS_PlayIndex);
        return (count($combatChain) > 0 ? "AR" : "A");
      case "ARC019": return "A";
      case "ARC035": return "I";
      case "ARC037": return "A";
      case "ARC038": case "ARC039": case "ARC040": case "ARC041": case "ARC042": return "A";
      case "ARC077": return "AA";
      case "ARC078": return "A";
      case "ARC079": return "A";
      case "ARC113": case "ARC114": case "ARC115": case "ARC116": return "I";
      case "ARC117": return "A";
      case "ARC151": return "I";
      case "ARC153": case "ARC154": return "A";
      default: return "";
    }
  }

  function ARCHasGoAgain($cardID)
  {
    global $currentPlayer, $CS_NumMoonWishPlayed;
    switch($cardID)
    {
      case "ARC006": return true;
      case "ARC014": case "ARC015": case "ARC016": return true;
      case "ARC032": case "ARC033": case "ARC034": return true;
      case "ARC044": case "ARC046": case "ARC047":
      case "ARC051": case "ARC052": case "ARC053":
      case "ARC054": case "ARC055": case "ARC056":
      case "ARC072": case "ARC073": case "ARC074": return true;
      case "ARC081": return true;
      case "ARC083": return true;
      case "ARC084": return true;
      case "ARC091": case "ARC092": case "ARC093": return true;
      case "ARC100": case "ARC101": case "ARC102": return true;
      case "ARC106": case "ARC107": case "ARC108": return true;
      case "ARC162": return true;
      case "ARC167": case "ARC168": case "ARC169": return true;
      case "ARC170": case "ARC171": case "ARC172": return true;
      case "ARC191": case "ARC192": case "ARC193": return true;
      case "ARC203": case "ARC204": case "ARC205": return true;
      case "ARC206": case "ARC207": case "ARC208": return true;
      case "ARC209": case "ARC210": case "ARC211": return true;
      case "ARC212": case "ARC213": case "ARC214": return GetClassState($currentPlayer, $CS_NumMoonWishPlayed) > 0;
      case "ARC215": case "ARC216": case "ARC217": return true;

      default: return false;
    }
  }

  function ARCAbilityHasGoAgain($cardID)
  {
    global $currentPlayer, $CS_PlayIndex, $combatChain;
    switch($cardID)
    {
      case "ARC003":
        $abilityType = GetResolvedAbilityType($cardID);
        return $abilityType == "A";
      case "ARC004": return true;
      case "ARC010":
        return count($combatChain) == 0;
      case "ARC017":
        $items = &GetItems($currentPlayer);
        return ($items[GetClassState($currentPlayer, $CS_PlayIndex) + 1] > 0 ? true : false);
      case "ARC018":
        $items = &GetItems($currentPlayer);
        return ($items[GetClassState($currentPlayer, $CS_PlayIndex) + 1] > 0 ? true : false);
      case "ARC019": return true;
      case "ARC037": return true;
      case "ARC038": case "ARC039": case "ARC040": case "ARC041": case "ARC042": return true;
      case "ARC078": return true;
      case "ARC153": case "ARC154": return true;
      default: return false;
    }
  }

  function ARCEffectAttackModifier($cardID)
  {
    switch($cardID)
    {
    case "ARC032": return 3;
    case "ARC033": return 2;
    case "ARC034": return 1;
    case "ARC042": return 1;
    case "ARC054": return 3;
    case "ARC055": return 2;
    case "ARC056": return 1;
    case "ARC057": case "ARC058": case "ARC059": return 2;
    case "ARC091": return 3;
    case "ARC092": return 2;
    case "ARC093": return 1;
    case "ARC153-1": return 1; case "ARC153-2": return 2; case "ARC153-3": return 3;
    case "ARC160-1": return 1;
    case "ARC170-2": return 3;
    case "ARC171-2": return 2;
    case "ARC172-2": return 1;
    case "ARC203": return 3;
    case "ARC204": return 2;
    case "ARC205": return 1;
    case "ARC206": return 3;
    case "ARC207": return 2;
    case "ARC208": return 1;
      default: return 0;
    }
  }

function ARCCombatEffectActive($cardID, $attackID)
{
  global $combatChainState, $CCS_AttackPlayedFrom, $mainPlayer;
  switch ($cardID) {
    case "ARC011": case "ARC012": case "ARC013":
      return true;
    case "ARC019":
      return CardType($attackID) == "AA";
    case "ARC032": case "ARC033": case "ARC034":
      return CardType($attackID) == "AA" && ClassContains($attackID, "MECHANOLOGIST", $mainPlayer);
    case "ARC038": case "ARC039":
      return CardSubType($attackID) == "Arrow" && $combatChainState[$CCS_AttackPlayedFrom] == "ARS";
    case "ARC042":
      return CardSubType($attackID) == "Arrow" && $combatChainState[$CCS_AttackPlayedFrom] == "ARS";
    case "ARC047":
      return CardSubType($attackID) == "Arrow";
    case "ARC054": case "ARC055": case "ARC056":
      return ClassContains($attackID, "RANGER", $mainPlayer) && CardType($attackID) == "AA";
    case "ARC057": case "ARC058": case "ARC059":
      return $cardID == $attackID;
    case "ARC091": case "ARC092": case "ARC093":
      return ClassContains($attackID, "RUNEBLADE", $mainPlayer);
    case "ARC153-1": case "ARC153-2": case "ARC153-3":
    case "ARC160-1": case "ARC160-3":
    case "ARC170-1": case "ARC171-1": case "ARC172-1":
    case "ARC170-2": case "ARC171-2": case "ARC172-2":
    case "ARC203": case "ARC204": case "ARC205":
    case "ARC206": case "ARC207": case "ARC208":
      return CardType($attackID) == "AA";
    default:
      return false;
  }
}

?>
