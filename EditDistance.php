<?php
/** 
 * EditDistance computes the similarity between two strings.
 */

  class EditDistance {

    public $table; // dynamic programming table

    public $s; // strings
    public $t;
      
    public $n; // string lengths
    public $m; 
      
    public $s_i; // character arrays
    public $t_i;

    /**
     * @method float computes the similarity between two strings as a value from 0.0 (no similarity) to 1.0 (perfect similarity).
     */
    function get_similarity() {
      if(!empty($this->table)) {
        $max_length = max($this->n, $this->m);
        $edit_distance = $this->table[$this->n][$this->m];
        return 1.0 - ($edit_distance / $max_length);                
      } else {
        return 0.0;
      }
    }

    /**
     * @method null computes the minimum number of typos needed to transform one string into another.
     * @param $string_in first string
     * @param $string_out second string
     */      
    function get_edit_distance($string_in, $string_out) {
      
      $this->table = array(array());
      
      $this->s = $string_in; // strings
      $this->t = $string_out;
      
      $this->n = strlen($string_in); // string lengths
      $this->m = strlen($string_out); 
      
      $this->s_i = str_split($string_in); // character arrays
      $this->t_i = str_split($string_out);
       
      // initialization    
      for($i=0; $i<=$this->n; $i++) {
        $this->table[$i][0] = $i;
      }
    
      for($j=0; $j<=$this->m; $j++) {
        $this->table[0][$j] = $j;
      }
    
      // computation
      for ($i=1; $i<=$this->n; $i++) {
     
        for ($j=1; $j<=$this->m; $j++) {

          if ($this->s_i[$i-1] == $this->t_i[$j-1]) {
            $cost = 0;
          } else {
            $cost = 1;
          }
        
          if($i>1 && $j>1 && $this->s_i[$i-1]==$this->t_i[$j-2] && $this->s_i[$i-2]==$this->t_i[$j-1] && $this->s_i[$i-1] != $this->s_i[$i-2]) {          
            $this->table[$i][$j] = min(
                              $this->table[$i-1][$j+1],
                              $this->table[$i][$j-1]+1,
                              $this->table[$i-2][$j-2]+1,
                              $this->table[$i-1][$j-1]+$cost
                              );
          } else {
            $this->table[$i][$j] = min(
                              $this->table[$i-1][$j]+1,
                              $this->table[$i][$j-1]+1,
                              $this->table[$i-1][$j-1]+$cost
                              );    
          }
        }
      }
      return $this->table[$this->n][$this->m]; // edit distance
    }     

    /**
     * @method string computes the edit transcript, which is a string describing the transformation of one string into another, letting
     * I denote the insert operation,
     * D denote the delete operation,
     * S denote the substitute operation,
     * M denote the match operation, and
     * R_ denote the reversal operation.
     */  
    function get_edit_transcript($string_in, $string_out) {

      $this->get_edit_distance($string_in, $string_out);
      
      $edit_string = ''; // edit transcript
      $i = $this->n;
      $j = $this->m;
      while($i>0 && $j>0) {
      
        if ($this->s_i[$i-1] == $this->t_i[$j-1]) {
          $cost = 0;
        } else {
          $cost = 1;
        }

        // deletion
        if ($this->table[$i][$j] == $this->table[$i-1][$j]+1) {
          $edit_string .= 'D';
          $i--;
        }
        
        // insertion
        elseif ($this->table[$i][$j] == $this->table[$i][$j-1]+1) {
          $edit_string .= 'I'; 
          $j--;
        }
            
        // match or substitution
        elseif ($this->table[$i][$j] == $this->table[$i-1][$j-1]+$cost) {
          if ($this->s_i[$i-1] == $this->t_i[$j-1]) {
            $edit_string .= 'M';
          } else {
            $edit_string .= 'S';
          }
          $i--; $j--;
        }
      
        // reversal
        else {
          $edit_string .= '_R';
          $i=$i-2; $j=$j-2;
        }           
      }
      while($i>0) {
       $edit_string .= 'D';
       $i--;
      }
      while($j>0) {
       $edit_string .= 'I';
       $j--;
      }
      return strrev($edit_string);
    }

    /**
     * @method string computes the positions of the edit operations
     * @param $string_in first string
     * @param $string_out second string
     */ 
    function get_edit_operations($string_in, $string_out) {
        
      $edit_string = $this->get_edit_transcript($string_in, $string_out); // edit transcript
      
      $length = strlen($edit_string); // length of edit transcript
      $results = array(); 
      $positions = array(); // positions
      $operations = array(); // transformations, e.g. insert  
      
      while($edit_string = strpbrk($edit_string, 'ISDR')) { // edit operations

        $positions[] = $length - strlen($edit_string) + 1;
        $operations[] = substr($edit_string, 0, 1);
         
        $edit_string = substr($edit_string, 1); // remove first character
      }
      $results[] = $positions;
      $results[] = $operations;
        
      return $results;
    }      
  
  }

?>
