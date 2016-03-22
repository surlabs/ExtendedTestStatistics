<?php

/**
 * Data model for basic evaluation results of a question in a test
 *
 * This data is calculated for all questions before the evaluations
 * It can be used to sort and slice the questions presented on a screen
 * The further evaluation are only calculated for those questions
 */
class ilExteStatSourceQuestion
{
	/**
	 @var integer 	question id
	 */
	public $question_id;

	/**
	 * @var integer	id of the original question
	 */
	public $original_id;

	/**
	 * @var string 		type tag of the question, e.g. 'assSingleChoice'
	 */
	public $question_type;

	/**
	 * @var integer 	question title
	 */
	public $title;

	/**
	 * @var float	maximum points that can be reached in the question
	 */
	public $maximum_points;

	/**
	 * @var	float	average of points that are reached by the participants answering the question
	 */
	public $average_points;

	/**
	 * @var	float 	average percentage of ???
	 */
	public $average_percentage;

	/**
	 * @var integer	number of users who answered the question
	 */
	public $answers_count;
}